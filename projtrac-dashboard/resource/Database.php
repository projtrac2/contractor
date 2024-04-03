<?php
//initialize variables to hold connection parameter
$config = require __DIR__ . '/../config/app.php';

$driver = $config['database']['driver'];
$host = $config['database']['host'];
$dbname = $config['database']['dbname'];
$db_username = $config['database']['username'];
$db_password = $config['database']['password'];

$dsn = "{$driver}:host={$host}; dbname={$dbname};charset=utf8mb4";
try {
    $db = new PDO($dsn, $db_username, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $ex) {
    $message = "Connection failed " . $ex->getMessage();
    
    error_log($message . PHP_EOL, 3, "error_log.txt");
}
