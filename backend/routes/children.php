<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

$method = api_require_method(['GET', 'POST', 'PUT', 'DELETE']);

api_handle_database_errors(function () use ($conn, $method) {
    if ($method === 'GET') {
        $userId = api_query_id('user_id');
        $id = api_query_id('id', false);

        if ($id !== null) {
            $statement = $conn->prepare(
                'SELECT id, user_id, name, age, delay_domain, notes, photo_url, created_at
                 FROM children WHERE id = ? AND user_id = ?'
            );
            $statement->bind_param('ii', $id, $userId);
            $statement->execute();
            $child = $statement->get_result()->fetch_assoc();
            if (!$child) {
                api_error(404, 'Child not found.');
            }
            api_success($child);
        }

        $statement = $conn->prepare(
            'SELECT id, user_id, name, age, delay_domain, notes, photo_url, created_at
             FROM children WHERE user_id = ? ORDER BY id'
        );
        $statement->bind_param('i', $userId);
        $statement->execute();
        api_success($statement->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    if ($method === 'POST' || $method === 'PUT') {
        $data = api_read_body();
        $userId = api_required_id($data, 'user_id');
        $name = api_required_string($data, 'name', 100);
        $age = api_required_id($data, 'age', 0);
        $delayDomain = api_optional_string($data, 'delay_domain', 100);
        $notes = api_optional_string($data, 'notes', 65535);
        $photoUrl = api_optional_string($data, 'photo_url', 255);

        if ($method === 'POST') {
            $statement = $conn->prepare(
                'INSERT INTO children (user_id, name, age, delay_domain, notes, photo_url)
                 SELECT id, ?, ?, ?, ?, ? FROM users WHERE id = ?'
            );
            $statement->bind_param('sisssi', $name, $age, $delayDomain, $notes, $photoUrl, $userId);
            $statement->execute();
            if ($statement->affected_rows === 0) {
                api_error(404, 'User not found.');
            }
            $id = $conn->insert_id;
            $statusCode = 201;
        } else {
            $id = api_query_id('id');
            $statement = $conn->prepare(
                'UPDATE children SET name = ?, age = ?, delay_domain = ?, notes = ?, photo_url = ?
                 WHERE id = ? AND user_id = ?'
            );
            $statement->bind_param('sisssii', $name, $age, $delayDomain, $notes, $photoUrl, $id, $userId);
            $statement->execute();
            $statusCode = 200;
        }

        $statement = $conn->prepare(
            'SELECT id, user_id, name, age, delay_domain, notes, photo_url, created_at
             FROM children WHERE id = ? AND user_id = ?'
        );
        $statement->bind_param('ii', $id, $userId);
        $statement->execute();
        $child = $statement->get_result()->fetch_assoc();
        if (!$child) {
            api_error(404, 'Child not found.');
        }
        api_success($child, $statusCode);
    }

    $id = api_query_id('id');
    $userId = api_query_id('user_id');
    $statement = $conn->prepare('DELETE FROM children WHERE id = ? AND user_id = ?');
    $statement->bind_param('ii', $id, $userId);
    $statement->execute();
    if ($statement->affected_rows === 0) {
        api_error(404, 'Child not found.');
    }

    api_success(['id' => $id]);
});
