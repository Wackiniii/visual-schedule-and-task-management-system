<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

$method = api_require_method(['GET', 'POST', 'PUT', 'DELETE']);

api_handle_database_errors(function () use ($conn, $method) {
    $select = 'SELECT s.id, s.child_id, s.date, s.created_at
               FROM schedules s JOIN children c ON c.id = s.child_id WHERE c.user_id = ?';

    if ($method === 'GET') {
        $userId = api_query_id('user_id');
        $id = api_query_id('id', false);
        $rows = api_select($conn, $select, $userId, ['s.id' => $id, 's.child_id' => api_query_id('child_id', false)], 's.date, s.id');
        if ($id === null) api_success($rows->fetch_all(MYSQLI_ASSOC));
        $schedule = $rows->fetch_assoc();
        if (!$schedule) api_error(404, 'Schedule not found.');
        api_success($schedule);
    }

    if ($method === 'DELETE') {
        $id = api_query_id('id');
        $userId = api_query_id('user_id');
        $delete = api_query($conn, 'DELETE s FROM schedules s JOIN children c ON c.id = s.child_id WHERE s.id = ? AND c.user_id = ?',
            'ii', [$id, $userId]);
        if ($delete->affected_rows === 0) api_error(404, 'Schedule not found.');
        api_success(['id' => $id]);
    }

    $data = api_read_body();
    $userId = api_body_id($data, 'user_id');
    $childId = api_body_id($data, 'child_id');
    $date = $data['date'] ?? null;
    $parsed = is_string($date) ? DateTime::createFromFormat('!Y-m-d', $date) : false;
    if (!$parsed || $parsed->format('Y-m-d') !== $date) api_error(400, 'date must use the YYYY-MM-DD format.');

    // the (new) child must belong to the user
    if (api_query($conn, 'SELECT id FROM children WHERE id = ? AND user_id = ?', 'ii', [$childId, $userId])->get_result()->num_rows === 0) {
        api_error(404, 'Child not found.');
    }

    if ($method === 'POST') {
        api_query($conn, 'INSERT INTO schedules (child_id, date) VALUES (?, ?)', 'is', [$childId, $date]);
        $id = $conn->insert_id;
        $code = 201;
    } else {
        $id = api_query_id('id');
        api_query($conn, 'UPDATE schedules SET child_id = ?, date = ?
                          WHERE id = ? AND child_id IN (SELECT id FROM children WHERE user_id = ?)', 'isii', [$childId, $date, $id, $userId]);
        $code = 200;
    }

    $schedule = api_select($conn, $select, $userId, ['s.id' => $id], 's.id')->fetch_assoc();
    if (!$schedule) api_error(404, 'Schedule not found.');
    api_success($schedule, $code);
});
