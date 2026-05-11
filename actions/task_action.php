<?php
session_start();
require_once '../backend/includes/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['ajax'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access.']);
        exit();
    }
    header("Location: /login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';

    // NEW: Check if the request is an AJAX background call
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;

    try {
        // ==========================================
        // 1. CREATE A NEW TASK
        // ==========================================
        if ($action === 'create') {
            $title = trim($_POST['title']);
            $course = trim($_POST['course']);
            $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $priority = $_POST['priority'];
            $status = 'pending';
            $reminder_value = !empty($_POST['reminder_value']) ? (int)$_POST['reminder_value'] : 0;
            $reminder_unit  = $_POST['reminder_unit'] ?? 'none';

            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, course, due_date, priority, status, reminder_value, reminder_unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $course, $due_date, $priority, $status, $reminder_value, $reminder_unit]);
        }

        // ==========================================
        // 2. UPDATE AN EXISTING TASK
        // ==========================================
        elseif ($action === 'update') {
            $task_id = $_POST['task_id'];
            $title = trim($_POST['title']);
            $course = trim($_POST['course']);
            $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $priority = $_POST['priority'];
            $status = $_POST['status'];
            $reminder_value = !empty($_POST['reminder_value']) ? (int)$_POST['reminder_value'] : 0;
            $reminder_unit  = $_POST['reminder_unit'] ?? 'none';

            $stmt = $pdo->prepare("UPDATE tasks SET title = ?, course = ?, due_date = ?, priority = ?, status = ?, reminder_value = ?, reminder_unit = ?, reminder_sent = 0 WHERE task_id = ? AND user_id = ?");
            $stmt->execute([$title, $course, $due_date, $priority, $status, $reminder_value, $reminder_unit, $task_id, $user_id]);
        }

        // ==========================================
        // 3. ARCHIVE A TASK (With Memory)
        // ==========================================
        elseif ($action === 'archive') {
            $task_id = $_POST['task_id'];

            $stmt = $pdo->prepare("UPDATE tasks SET previous_status = status, status = 'archived' WHERE task_id = ? AND user_id = ?");
            $stmt->execute([$task_id, $user_id]);
        }

        // ==========================================
        // 4. UNARCHIVE (RESTORE) A TASK
        // ==========================================
        elseif ($action === 'unarchive') {
            $task_id = $_POST['task_id'];

            $stmt = $pdo->prepare("UPDATE tasks SET status = COALESCE(previous_status, 'pending'), previous_status = NULL WHERE task_id = ? AND user_id = ?");
            $stmt->execute([$task_id, $user_id]);
        }

        // ==========================================
        // SUCCESS HANDLING
        // ==========================================
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Action completed successfully.']);
            exit();
        }

        header("Location: /views/tasks.php");
        exit();
    } catch (PDOException $e) {
        error_log("TalaAral Task Action Error ($action): " . $e->getMessage());

        if ($is_ajax) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database failure.']);
            exit();
        }

        header("Location: /views/tasks.php?error=db_error");
        exit();
    }
} else {
    header("Location: /views/tasks.php");
    exit();
}
