<?php

$databaseHost = getenv('DB_HOST') ?: 'localhost';
$databaseName = getenv('DB_NAME') ?: 'university_db';
$databaseUser = getenv('DB_USER') ?: 'root';
$databasePass = getenv('DB_PASSWORD');
$databasePort = getenv('DB_PORT');

if ($databasePass === false) {
    $databasePass = '';
}

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $databaseHost,
    $databaseName
);

if ($databasePort !== false && $databasePort !== '') {
    $dsn .= ';port=' . (int) $databasePort;
}

try {
    $pdo = new PDO($dsn, $databaseUser, $databasePass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('Sorry, the registration system is temporarily unavailable. Please try again later.');
}
