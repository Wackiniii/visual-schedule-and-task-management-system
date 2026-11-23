<?php
/*
 * ADDED FILE: backend/routes/health.php
 * Connection check used by frontend/db-check.html. Reports whether MySQL is
 * reachable, whether visual_schedule_db exists, and how many rows each table has.
 * Reuses config/db.php so credentials stay in one place.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // make connection failures throw instead of printing HTML

try {
    require_once __DIR__ . '/../config/db.php'; // sets CORS headers, creates $conn
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    echo json_encode([
        "status" => "error",
        "message" => "Cannot connect to the database: " . $e->getMessage() .
            ". Start MySQL in XAMPP and import database/schema.sql."
    ]);
    exit();
}

$tables = [];
foreach (['users', 'children', 'schedules', 'tasks'] as $table) {
    try {
        $row = $conn->query("SELECT COUNT(*) AS total FROM `$table`")->fetch_assoc();
        $tables[$table] = (int) $row['total'];
    } catch (mysqli_sql_exception $e) {
        $tables[$table] = null; // table is missing
    }
}

echo json_encode([
    "status" => "success",
    "data" => [
        "database" => $dbname,
        "server" => $conn->server_info,
        "tables" => $tables
    ]
]);
