<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

$method = api_require_method(['GET', 'POST', 'PUT', 'DELETE']);

api_handle_database_errors(function () use ($conn, $method) {
    $owned = 'FROM tasks t JOIN schedules s ON s.id = t.schedule_id JOIN children c ON c.id = s.child_id';
    $select = "SELECT t.id, t.schedule_id, t.name, t.icon, t.time, t.duration, t.status, t.completed_at $owned WHERE c.user_id = ?";

    if ($method === 'GET') {
        $userId = api_query_id('user_id');
        $id = api_query_id('id', false);
        $rows = api_select($conn, $select, $userId, ['t.id' => $id, 't.schedule_id' => api_query_id('schedule_id', false)], 't.id');
        if ($id === null) api_success($rows->fetch_all(MYSQLI_ASSOC));
        $task = $rows->fetch_assoc();
        if (!$task) api_error(404, 'Task not found.');
        api_success($task);
    }

    if ($method === 'DELETE') {
        $id = api_query_id('id');
        $userId = api_query_id('user_id');
        if (api_query($conn, "DELETE t $owned WHERE t.id = ? AND c.user_id = ?", 'ii', [$id, $userId])->affected_rows === 0) {
            api_error(404, 'Task not found.');
        }
        api_success(['id' => $id]);
    }

    $data = api_read_body();
    $userId = api_body_id($data, 'user_id');
    $scheduleId = api_body_id($data, 'schedule_id');
    $name = api_string($data, 'name', 150, true);
    $icon = array_key_exists('icon', $data) ? api_string($data, 'icon', 100) : 'fa-tasks';
    $time = api_time($data);
    $duration = api_duration($data);
    $status = $data['status'] ?? 'pending';
    if (!in_array($status, ['pending', 'completed'], true)) api_error(400, 'status must be pending or completed.');

    // the (new) schedule must belong to the user
    $mine = api_query($conn, 'SELECT s.id FROM schedules s JOIN children c ON c.id = s.child_id WHERE s.id = ? AND c.user_id = ?',
        'ii', [$scheduleId, $userId]);
    if ($mine->get_result()->num_rows === 0) api_error(404, 'Schedule not found.');

    $fields = [$scheduleId, $name, $icon, $time, $duration, $status, $status];
    if ($method === 'POST') {
        api_query($conn, "INSERT INTO tasks (schedule_id, name, icon, time, duration, status, completed_at)
                          VALUES (?, ?, ?, ?, ?, ?, IF(? = 'completed', NOW(), NULL))", 'isssiss', $fields);
        $id = $conn->insert_id;
        $code = 201;
    } else {
        $id = api_query_id('id');
        api_query($conn, "UPDATE tasks t JOIN schedules s ON s.id = t.schedule_id JOIN children c ON c.id = s.child_id
                          SET t.schedule_id = ?, t.name = ?, t.icon = ?, t.time = ?, t.duration = ?, t.status = ?,
                              t.completed_at = IF(? = 'completed', COALESCE(t.completed_at, NOW()), NULL)
                          WHERE t.id = ? AND c.user_id = ?", 'isssissii', array_merge($fields, [$id, $userId]));
        $code = 200;
    }

    $task = api_select($conn, $select, $userId, ['t.id' => $id], 't.id')->fetch_assoc();
    if (!$task) api_error(404, 'Task not found.');
    api_success($task, $code);
});
