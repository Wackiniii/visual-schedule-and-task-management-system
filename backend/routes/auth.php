<?php
require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $role = trim($data['role'] ?? 'Caregiver');

    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Name, email, and password are required."]);
        exit();
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Email is already registered."]);
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password_hash, $role);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "User registered successfully.",
            "user_id" => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Database insert failed: " . $stmt->error
        ]);
    }

} elseif ($action === 'login') {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            unset($row['password_hash']);
            echo json_encode(["status" => "success", "user" => $row]);
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid action specified."]);
}
?>