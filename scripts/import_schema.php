<?php
// Simple schema import script for local development
require_once __DIR__ . '/../app/config/config.php';

$sqlFile = __DIR__ . '/../database/schema.sql';
if (!file_exists($sqlFile)) {
    fwrite(STDERR, "schema.sql not found at {$sqlFile}\n");
    exit(1);
}

$host = DB_HOST;
$user = DB_USERNAME;
$pass = DB_PASSWORD;
$port = (int) DB_PORT;

$mysqli = new mysqli($host, $user, $pass, '', $port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL connection failed: ({$mysqli->connect_errno}) {$mysqli->connect_error}\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Failed to read schema file.\n");
    exit(1);
}

if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Database import completed successfully.\n";
    exit(0);
} else {
    fwrite(STDERR, "Import failed: {$mysqli->error}\n");
    exit(1);
}
