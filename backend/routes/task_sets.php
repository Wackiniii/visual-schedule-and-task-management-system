<?php
// Reusable task sets: a named list of {name, icon, time, duration} a user can apply to any day.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

$method = api_require_method(['GET', 'POST', 'DELETE']);

api_handle_database_errors(function () use ($conn, $method) {
    if ($method === 'GET') {
        $userId = api_query_id('user_id');
        $sets = api_query($conn, 'SELECT id, name, tasks, created_at FROM task_sets WHERE user_id = ? ORDER BY id', 'i', [$userId])
            ->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($sets as $i => $set) $sets[$i]['tasks'] = json_decode($set['tasks'], true);
        api_success($sets);
    }

    if ($method === 'DELETE') {
        $id = api_query_id('id');
        $userId = api_query_id('user_id');
        if (api_query($conn, 'DELETE FROM task_sets WHERE id = ? AND user_id = ?', 'ii', [$id, $userId])->affected_rows === 0) {
            api_error(404, 'Task set not found.');
        }
        api_success(['id' => $id]);
    }

    $data = api_read_body();
    $userId = api_body_id($data, 'user_id');
    $name = api_string($data, 'name', 100, true);
    $input = $data['tasks'] ?? null;
    if (!is_array($input) || count($input) < 1 || count($input) > 50) api_error(400, 'tasks must be a list of 1 to 50 tasks.');

    $tasks = [];
    foreach ($input as $task) {
        if (!is_array($task)) api_error(400, 'Each task must be an object.');
        $tasks[] = ['name' => api_string($task, 'name', 150, true), 'icon' => api_string($task, 'icon', 100),
            'time' => api_time($task), 'duration' => api_duration($task)];
    }

    $insert = api_query($conn, 'INSERT INTO task_sets (user_id, name, tasks) SELECT id, ?, ? FROM users WHERE id = ?',
        'ssi', [$name, json_encode($tasks), $userId]);
    if ($insert->affected_rows === 0) api_error(404, 'User not found.');
    api_success(['id' => $conn->insert_id, 'name' => $name, 'tasks' => $tasks], 201);
});
