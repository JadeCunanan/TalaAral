<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

$user_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Manila');
$phpNow = date('Y-m-d H:i:s');

try {
    // 1. Mark overdue tasks
    $pdo->prepare("
        UPDATE tasks SET previous_status = status, status = 'missed' 
        WHERE user_id = ? 
        AND status IN ('pending', 'in_progress') 
        AND due_date IS NOT NULL 
        AND due_date < ?
    ")->execute([$user_id, $phpNow]);

    // 2. Calculate Stats (Splitting Pending and In Progress)
    $stmt = $pdo->prepare("SELECT status, due_date FROM tasks WHERE user_id = ? AND status != 'archived'");
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll();

    $stats = [
        'pending'     => 0, 
        'in_progress' => 0, 
        'completed'   => 0, 
        'overdue'     => 0, 
        'today'       => 0
    ];

    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd   = date('Y-m-d 23:59:59');

    foreach ($tasks as $t) {
        if ($t['status'] === 'completed') {
            $stats['completed']++;
        } else {
            // Count specific statuses
            if ($t['status'] === 'pending' || $t['status'] === 'missed') $stats['pending']++;
            if ($t['status'] === 'in_progress') $stats['in_progress']++;
            if ($t['status'] === 'missed') $stats['overdue']++;

            // Check if due today
            if ($t['due_date'] && $t['due_date'] >= $todayStart && $t['due_date'] <= $todayEnd && $t['status'] !== 'missed') {
                $stats['today']++;
            }
        }
    }

    // 3. Fetch Upcoming Deadlines
    $stmt = $pdo->prepare("
        SELECT title, course, due_date, priority FROM tasks 
        WHERE user_id = ? 
        AND status IN ('pending', 'in_progress') 
        AND due_date >= ? 
        ORDER BY due_date ASC LIMIT 3
    ");
    $stmt->execute([$user_id, $phpNow]);

    echo json_encode([
        'stats' => $stats,
        'upcoming' => $stmt->fetchAll()
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}