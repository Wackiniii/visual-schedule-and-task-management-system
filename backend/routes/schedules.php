<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';

$method = api_require_method(['GET', 'POST', 'PUT', 'DELETE']);

api_handle_database_errors(function () use ($conn, $method) {
    if ($method === 'GET') {
        $userId = api_query_id('user_id');
        $id = api_query_id('id', false);
        $childId = api_query_id('child_id', false);
        $sql = 'SELECT s.id, s.child_id, s.date, s.created_at
                FROM schedules s
                INNER JOIN children c ON c.id = s.child_id
                WHERE c.user_id = ?';
        $types = 'i';
        $params = [$userId];

        if ($id !== null) {
            $sql .= ' AND s.id = ?';
            $types .= 'i';
            $params[] = $id;
        }
        if ($childId !== null) {
            $sql .= ' AND s.child_id = ?';
            $types .= 'i';
            $params[] = $childId;
        }
        $sql .= ' ORDER BY s.date, s.id';

        $statement = $conn->prepare($sql);
        api_bind_params($statement, $types, $params);
        $statement->execute();
        $result = $statement->get_result();
        if ($id !== null) {
            $schedule = $result->fetch_assoc();
            if (!$schedule) {
                api_error(404, 'Schedule not found.');
            }
            api_success($schedule);
        }
        api_success($result->fetch_all(MYSQLI_ASSOC));
    }

    if ($method === 'POST' || $method === 'PUT') {
        $data = api_read_body();
        $userId = api_required_id($data, 'user_id');
        $childId = api_required_id($data, 'child_id');
        $date = $data['date'] ?? null;
        $parsedDate = is_string($date) ? DateTime::createFromFormat('!Y-m-d', $date) : false;
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            api_error(400, 'date must use the YYYY-MM-DD format.');
        }

        if ($method === 'POST') {
            $statement = $conn->prepare(
                'INSERT INTO schedules (child_id, date)
                 SELECT id, ? FROM children WHERE id = ? AND user_id = ?'
            );
            $statement->bind_param('sii', $date, $childId, $userId);
            $statement->execute();
            if ($statement->affected_rows === 0) {
                api_error(404, 'Child not found.');
            }
            $id = $conn->insert_id;
            $statusCode = 201;
        } else {
            $id = api_query_id('id');
            $statement = $conn->prepare(
                'UPDATE schedules s
                 INNER JOIN children current_child
                    ON current_child.id = s.child_id AND current_child.user_id = ?
                 INNER JOIN children target_child
                    ON target_child.id = ? AND target_child.user_id = ?
                 SET s.child_id = target_child.id, s.date = ?
                 WHERE s.id = ?'
            );
            $statement->bind_param('iiisi', $userId, $childId, $userId, $date, $id);
            $statement->execute();
            $statusCode = 200;
        }

        $statement = $conn->prepare(
            'SELECT s.id, s.child_id, s.date, s.created_at
             FROM schedules s
             INNER JOIN children c ON c.id = s.child_id
             WHERE s.id = ? AND c.user_id = ? AND s.child_id = ? AND s.date = ?'
        );
        $statement->bind_param('iiis', $id, $userId, $childId, $date);
        $statement->execute();
        $schedule = $statement->get_result()->fetch_assoc();
        if (!$schedule) {
            api_error(404, 'Schedule not found.');
        }
        api_success($schedule, $statusCode);
    }

    $id = api_query_id('id');
    $userId = api_query_id('user_id');
    $statement = $conn->prepare(
        'DELETE s FROM schedules s
         INNER JOIN children c ON c.id = s.child_id
         WHERE s.id = ? AND c.user_id = ?'
    );
    $statement->bind_param('ii', $id, $userId);
    $statement->execute();
    if ($statement->affected_rows === 0) {
        api_error(404, 'Schedule not found.');
    }
    api_success(['id' => $id]);
});
