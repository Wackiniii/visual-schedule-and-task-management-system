<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

$method = api_require_method(['GET', 'POST', 'PUT', 'DELETE']);

api_handle_database_errors(function () use ($conn, $method) {
    $select = 'SELECT c.id, c.user_id, c.name, c.age, c.delay_domain, c.notes, c.photo_url, c.created_at
               FROM children c WHERE c.user_id = ?';

    if ($method === 'GET') {
        $userId = api_query_id('user_id');
        $id = api_query_id('id', false);
        $rows = api_select($conn, $select, $userId, ['c.id' => $id], 'c.id');
        if ($id === null) api_success($rows->fetch_all(MYSQLI_ASSOC));
        $child = $rows->fetch_assoc();
        if (!$child) api_error(404, 'Child not found.');
        api_success($child);
    }

    if ($method === 'DELETE') {
        $id = api_query_id('id');
        $userId = api_query_id('user_id');
        if (api_query($conn, 'DELETE FROM children WHERE id = ? AND user_id = ?', 'ii', [$id, $userId])->affected_rows === 0) {
            api_error(404, 'Child not found.');
        }
        api_success(['id' => $id]);
    }

    $data = api_read_body();
    $userId = api_body_id($data, 'user_id');
    $fields = [api_string($data, 'name', 100, true), api_body_id($data, 'age', 0), api_string($data, 'delay_domain', 100),
        api_string($data, 'notes', 65535), api_string($data, 'photo_url', 255)];

    if ($method === 'POST') {
        $insert = api_query($conn, 'INSERT INTO children (name, age, delay_domain, notes, photo_url, user_id)
                                    SELECT ?, ?, ?, ?, ?, id FROM users WHERE id = ?',
            'sisssi', array_merge($fields, [$userId]));
        if ($insert->affected_rows === 0) api_error(404, 'User not found.');
        $id = $conn->insert_id;
        $code = 201;
    } else {
        $id = api_query_id('id');
        api_query($conn, 'UPDATE children SET name = ?, age = ?, delay_domain = ?, notes = ?, photo_url = ?
                          WHERE id = ? AND user_id = ?', 'sisssii', array_merge($fields, [$id, $userId]));
        $code = 200;
    }

    $child = api_select($conn, $select, $userId, ['c.id' => $id], 'c.id')->fetch_assoc();
    if (!$child) api_error(404, 'Child not found.');
    api_success($child, $code);
});
