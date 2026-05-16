<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../includes/db.php';

$is_preview = isset($_GET['preview']) && $_GET['preview'] === '1';
$limit = $is_preview ? 3 : 20;

try {
    $stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY posted_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($announcements);
} catch (Exception $e) {
    error_log("Announcements Error: " . $e->getMessage());
    echo json_encode([]);
}