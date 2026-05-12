<?php
session_start();
error_reporting(0);
header('Content-Type: application/json');

require_once '../backend/includes/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');

// 2. Validation
if (empty($full_name)) {
    echo json_encode(['success' => false, 'error' => 'Full Name cannot be empty.']);
    exit();
}

try {
    if (!isset($pdo)) throw new Exception("Database connection failed.");

    // 3. Update ONLY the full_name
    // We remove the email from the SET clause entirely.
    $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
    
    if ($stmt->execute([$full_name, $user_id])) {
        
        // Sync the session memory
        $_SESSION['full_name'] = $full_name;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Name updated successfully!',
            'new_name' => $full_name 
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No changes were made.']);
    }
} catch (Exception $e) {
    error_log("TalaAral Profile Update Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'A database error occurred.']);
}
?>