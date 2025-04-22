<?php
$db_host = 'localhost'; // Or 127.0.0.1
$db_name = 'event_calendar_db'; // Your database name
$db_user = 'root'; // Your DB username
$db_pass = ''; // Your DB password

// Enable error reporting for debugging (consider changing in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // error_log("Database Connection Error: " . $e->getMessage()); // Log in production
    die("Database connection failed. Check credentials/server. Error: " . $e->getMessage());
}