<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

const MAX_FAILED_LOGINS = 5;
const LOCK_MINUTES = 5;

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

api_handle_database_errors(function () use ($conn, $data, $email, $password) {
    $action = $_GET['action'] ?? '';

    if ($action === 'register') {
        $name = trim($data['name'] ?? '');
        $role = trim($data['role'] ?? 'Caregiver');
        if ($name === '' || $email === '' || $password === '') {
            api_error(400, 'Name, email, and password are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) api_error(400, 'Enter a valid email address.');
        if ($error = api_password_error($password)) api_error(400, $error);
        if (api_query($conn, 'SELECT id FROM users WHERE email = ?', 's', [$email])->get_result()->num_rows > 0) {
            api_error(409, 'Email is already registered.');
        }
        api_query($conn, 'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)', 'ssss',
            [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "User registered successfully.", "user_id" => $conn->insert_id]);
    } elseif ($action === 'login') {
        if ($email === '' || $password === '') api_error(400, 'Please fill in all fields.');
        $user = api_query($conn, 'SELECT id, name, email, password_hash, role, failed_attempts, locked_until,
                                         COALESCE(TIMESTAMPDIFF(SECOND, NOW(), locked_until), 0) AS lock_seconds
                                  FROM users WHERE email = ?', 's', [$email])->get_result()->fetch_assoc();
        // same message for unknown email and wrong password, so emails cannot be probed
        if (!$user) api_error(401, 'Invalid email or password.');
        if ($user['lock_seconds'] > 0) {
            api_error(429, 'Too many failed attempts. Try again in ' . ceil($user['lock_seconds'] / 60) . ' minute(s).');
        }
        if (!password_verify($password, $user['password_hash'])) {
            $failed = ($user['locked_until'] === null ? $user['failed_attempts'] : 0) + 1; // an expired lock restarts the count
            $lock = $failed >= MAX_FAILED_LOGINS;
            api_query($conn, 'UPDATE users SET failed_attempts = ?, locked_until = IF(?, NOW() + INTERVAL ' . LOCK_MINUTES . ' MINUTE, NULL)
                              WHERE id = ?', 'iii', [$failed, (int) $lock, $user['id']]);
            if ($lock) api_error(429, 'Too many failed attempts. Try again in ' . LOCK_MINUTES . ' minutes.');
            api_error(401, 'Invalid email or password.');
        }
        if ($user['failed_attempts'] > 0 || $user['locked_until'] !== null) {
            api_query($conn, 'UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?', 'i', [$user['id']]);
        }
        $profile = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
        echo json_encode(["status" => "success", "user" => $profile]);
    } else {
        api_error(400, 'Invalid action specified.');
    }
});
