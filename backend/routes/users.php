<?php
/*
 * ADDED FILE: backend/routes/users.php
 * GET ?id=<user-id>&email=<email>  ->  confirms the logged-in user still exists in
 * the database (e.g. after the schema was re-imported) and returns fresh name/role.
 * Both id AND email must match, so ids cannot be enumerated to read other users.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

api_require_method(['GET']);

api_handle_database_errors(function () use ($conn) {
    $id = api_query_id('id');
    $email = isset($_GET['email']) ? trim((string) $_GET['email']) : '';
    if ($email === '') {
        api_error(400, 'email is required.');
    }

    $statement = $conn->prepare('SELECT id, name, email, role FROM users WHERE id = ? AND email = ?');
    $statement->bind_param('is', $id, $email);
    $statement->execute();
    $user = $statement->get_result()->fetch_assoc();
    if (!$user) {
        api_error(404, 'User not found.');
    }
    api_success($user);
});
