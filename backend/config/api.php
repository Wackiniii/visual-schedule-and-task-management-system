<?php
// Shared JSON helpers. Responses: {"status":"success","data":...} or {"status":"error","message":...}
function api_success($data, int $code = 200): void { http_response_code($code); echo json_encode(["status" => "success", "data" => $data]); exit(); }
function api_error(int $code, string $message): void { http_response_code($code); echo json_encode(["status" => "error", "message" => $message]); exit(); }

function api_require_method(array $allowed): string
{
    if (!in_array($_SERVER['REQUEST_METHOD'], $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        api_error(405, 'Method not allowed.');
    }
    return $_SERVER['REQUEST_METHOD'];
}

function api_read_body(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) api_error(400, 'Request body must be valid JSON.');
    return $data;
}

function api_id($value, string $name, int $min = 1): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id < $min) api_error(400, "$name must be a valid integer.");
    return $id;
}

function api_body_id(array $d, string $name, int $min = 1): int
{
    if (!array_key_exists($name, $d)) api_error(400, "$name is required.");
    return api_id($d[$name], $name, $min);
}

function api_query_id(string $name, bool $required = true): ?int
{
    $value = $_GET[$name] ?? '';
    if ($value === '') {
        if ($required) api_error(400, "$name is required.");
        return null;
    }
    return api_id($value, $name);
}

// String field up to $max chars; required ones are trimmed and must be non-empty, optional ones may be null/''.
function api_string(array $d, string $name, int $max, bool $required = false): ?string
{
    $value = $d[$name] ?? null;
    if ($required && is_string($value)) $value = trim($value);
    if ($value === null || $value === '') {
        if ($required) api_error(400, "$name is required.");
        return null;
    }
    if (!is_string($value) || strlen($value) > $max) api_error(400, "$name must be a string up to $max characters.");
    return $value;
}

function api_query(mysqli $conn, string $sql, string $types, array $params): mysqli_stmt
{
    $statement = $conn->prepare($sql);
    $statement->bind_param($types, ...$params);
    $statement->execute();
    return $statement;
}

// Rows the user owns: $sql ends in "WHERE <owner column> = ?"; each non-null $filters entry adds " AND col = ?".
function api_select(mysqli $conn, string $sql, int $userId, array $filters, string $order): mysqli_result
{
    $types = 'i';
    $params = [$userId];
    foreach ($filters as $column => $value) {
        if ($value !== null) {
            $sql .= " AND $column = ?";
            $types .= 'i';
            $params[] = $value;
        }
    }
    return api_query($conn, "$sql ORDER BY $order", $types, $params)->get_result();
}

function api_handle_database_errors(callable $handler): void
{
    try {
        $handler();
    } catch (mysqli_sql_exception $error) {
        error_log('Backend database error: ' . $error->getMessage());
        api_error(500, 'A database error occurred.');
    }
}
