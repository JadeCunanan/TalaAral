<?php
$host = getenv('DB_HOST') ?: 'db'; 
$port = getenv('DB_PORT') ?: '3306'; // This is the key line!
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("The system is currently unable to connect to the database.");
}
?>