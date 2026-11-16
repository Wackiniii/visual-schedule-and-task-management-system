<?php

function api_success($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode(["status" => "success", "data" => $data]);
    exit();
}

function api_error(int $statusCode, string $message): void
{
    http_response_code($statusCode);
    echo json_encode(["status" => "error", "message" => $message]);
    exit();
}

function api_require_method(array $allowedMethods): string
{
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, $allowedMethods, true)) {
        header('Allow: ' . implode(', ', $allowedMethods));
        api_error(405, 'Method not allowed.');
    }

    return $method;
}

function api_read_body(): array
{
    $body = file_get_contents('php://input');
    if ($body === false || trim($body) === '') {
        api_error(400, 'A JSON request body is required.');
    }

    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        api_error(400, 'Request body must be valid JSON.');
    }

    return $data;
}

function api_id($value, string $name, int $minimum = 1): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id < $minimum) {
        api_error(400, $name . ' must be a valid integer.');
    }

    return $id;
}

function api_required_id(array $data, string $name, int $minimum = 1): int
{
    if (!array_key_exists($name, $data)) {
        api_error(400, $name . ' is required.');
    }

    return api_id($data[$name], $name, $minimum);
}

function api_query_id(string $name, bool $required = true): ?int
{
    if (!isset($_GET[$name]) || $_GET[$name] === '') {
        if ($required) {
            api_error(400, $name . ' is required.');
        }

        return null;
    }

    return api_id($_GET[$name], $name);
}

function api_required_string(array $data, string $name, int $maxLength): string
{
    if (!isset($data[$name]) || !is_string($data[$name])) {
        api_error(400, $name . ' is required and must be a string.');
    }

    $value = trim($data[$name]);
    if ($value === '' || strlen($value) > $maxLength) {
        api_error(400, $name . ' must be between 1 and ' . $maxLength . ' characters.');
    }

    return $value;
}

function api_optional_string(array $data, string $name, int $maxLength): ?string
{
    if (!array_key_exists($name, $data) || $data[$name] === null || $data[$name] === '') {
        return null;
    }

    if (!is_string($data[$name]) || strlen($data[$name]) > $maxLength) {
        api_error(400, $name . ' must be a string no longer than ' . $maxLength . ' characters.');
    }

    return $data[$name];
}

function api_bind_params(mysqli_stmt $statement, string $types, array &$params): void
{
    $arguments = [$types];
    foreach ($params as &$param) {
        $arguments[] = &$param;
    }
    unset($param);

    call_user_func_array([$statement, 'bind_param'], $arguments);
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

