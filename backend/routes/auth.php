<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

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
        if (api_query($conn, 'SELECT id FROM users WHERE email = ?', 's', [$email])->get_result()->num_rows > 0) {
            api_error(409, 'Email is already registered.');
        }
        api_query($conn, 'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)', 'ssss',
            [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "User registered successfully.", "user_id" => $conn->insert_id]);
    } elseif ($action === 'login') {
        if ($email === '' || $password === '') api_error(400, 'Please fill in all fields.');
        $user = api_query($conn, 'SELECT id, name, email, password_hash, role FROM users WHERE email = ?', 's', [$email])
            ->get_result()->fetch_assoc();
        if (!$user) api_error(401, 'User not found.');
        if (!password_verify($password, $user['password_hash'])) api_error(401, 'Invalid credentials.');
        unset($user['password_hash']);
        echo json_encode(["status" => "success", "user" => $user]);
    } else {
        api_error(400, 'Invalid action specified.');
    }
});
