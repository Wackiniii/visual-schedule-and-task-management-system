<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

$method = api_require_method(['GET', 'POST', 'PUT', 'DELETE']);

api_handle_database_errors(function () use ($conn, $method) {
    if ($method === 'GET') {
        $userId = api_query_id('user_id');
        $id = api_query_id('id', false);
        $scheduleId = api_query_id('schedule_id', false);
        $sql = 'SELECT t.id, t.schedule_id, t.name, t.icon, t.time, t.duration,
                       t.status, t.completed_at
                FROM tasks t
                INNER JOIN schedules s ON s.id = t.schedule_id
                INNER JOIN children c ON c.id = s.child_id
                WHERE c.user_id = ?';
        $types = 'i';
        $params = [$userId];

        if ($id !== null) {
            $sql .= ' AND t.id = ?';
            $types .= 'i';
            $params[] = $id;
        }
        if ($scheduleId !== null) {
            $sql .= ' AND t.schedule_id = ?';
            $types .= 'i';
            $params[] = $scheduleId;
        }
        $sql .= ' ORDER BY t.id';

        $statement = $conn->prepare($sql);
        api_bind_params($statement, $types, $params);
        $statement->execute();
        $result = $statement->get_result();
        if ($id !== null) {
            $task = $result->fetch_assoc();
            if (!$task) {
                api_error(404, 'Task not found.');
            }
            api_success($task);
        }
        api_success($result->fetch_all(MYSQLI_ASSOC));
    }

    if ($method === 'POST' || $method === 'PUT') {
        $data = api_read_body();
        $userId = api_required_id($data, 'user_id');
        $scheduleId = api_required_id($data, 'schedule_id');
        $name = api_required_string($data, 'name', 150);
        $icon = array_key_exists('icon', $data)
            ? api_optional_string($data, 'icon', 100)
            : 'fa-tasks';
        $time = api_optional_string($data, 'time', 8);
        if ($time !== null) {
            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time)) {
                api_error(400, 'time must use HH:MM or HH:MM:SS format.');
            }
            if (strlen($time) === 5) {
                $time .= ':00';
            }
        }
        $duration = null;
        if (array_key_exists('duration', $data) && $data['duration'] !== null && $data['duration'] !== '') {
            $duration = api_id($data['duration'], 'duration', 0);
        }
        $status = $data['status'] ?? 'pending';
        if (!is_string($status) || !in_array($status, ['pending', 'completed'], true)) {
            api_error(400, 'status must be pending or completed.');
        }

        if ($method === 'POST') {
            $statement = $conn->prepare(
                "INSERT INTO tasks (schedule_id, name, icon, time, duration, status, completed_at)
                 SELECT s.id, ?, ?, ?, ?, ?, IF(? = 'completed', NOW(), NULL)
                 FROM schedules s
                 INNER JOIN children c ON c.id = s.child_id
                 WHERE s.id = ? AND c.user_id = ?"
            );
            $statement->bind_param(
                'sssissii',
                $name,
                $icon,
                $time,
                $duration,
                $status,
                $status,
                $scheduleId,
                $userId
            );
            $statement->execute();
            if ($statement->affected_rows === 0) {
                api_error(404, 'Schedule not found.');
            }
            $id = $conn->insert_id;
            $statusCode = 201;
        } else {
            $id = api_query_id('id');
            $statement = $conn->prepare(
                "UPDATE tasks t
                 INNER JOIN schedules current_schedule ON current_schedule.id = t.schedule_id
                 INNER JOIN children current_child ON current_child.id = current_schedule.child_id
                 INNER JOIN schedules target_schedule ON target_schedule.id = ?
                 INNER JOIN children target_child
                    ON target_child.id = target_schedule.child_id AND target_child.user_id = ?
                 SET t.schedule_id = target_schedule.id, t.name = ?, t.icon = ?, t.time = ?,
                     t.duration = ?, t.status = ?,
                     t.completed_at = IF(? = 'completed', COALESCE(t.completed_at, NOW()), NULL)
                 WHERE t.id = ? AND current_child.user_id = ?"
            );
            $statement->bind_param(
                'iisssissii',
                $scheduleId,
                $userId,
                $name,
                $icon,
                $time,
                $duration,
                $status,
                $status,
                $id,
                $userId
            );
            $statement->execute();
            $statusCode = 200;
        }

        $statement = $conn->prepare(
            'SELECT t.id, t.schedule_id, t.name, t.icon, t.time, t.duration,
                    t.status, t.completed_at
             FROM tasks t
             INNER JOIN schedules s ON s.id = t.schedule_id
             INNER JOIN children c ON c.id = s.child_id
             WHERE t.id = ? AND c.user_id = ? AND t.schedule_id = ?'
        );
        $statement->bind_param('iii', $id, $userId, $scheduleId);
        $statement->execute();
        $task = $statement->get_result()->fetch_assoc();
        if (!$task) {
            api_error(404, 'Task or schedule not found.');
        }
        api_success($task, $statusCode);
    }

    $id = api_query_id('id');
    $userId = api_query_id('user_id');
    $statement = $conn->prepare(
        'DELETE t FROM tasks t
         INNER JOIN schedules s ON s.id = t.schedule_id
         INNER JOIN children c ON c.id = s.child_id
         WHERE t.id = ? AND c.user_id = ?'
    );
    $statement->bind_param('ii', $id, $userId);
    $statement->execute();
    if ($statement->affected_rows === 0) {
        api_error(404, 'Task not found.');
    }
    api_success(['id' => $id]);
});
