<?php
session_start();
// Prevent PHP warnings from breaking our JSON response
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
$email = trim($_POST['email'] ?? ''); // Read-only on frontend, but received here

// 2. Basic Validation
if (empty($full_name)) {
    echo json_encode(['success' => false, 'error' => 'Full Name cannot be empty.']);
    exit();
}

try {
    if (!isset($pdo)) throw new Exception("Database connection failed.");

    // 3. Update the database
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $email, $user_id])) {
        
        // Update the session so the sidebar and header reflect the new name on refresh
        $_SESSION['full_name'] = $full_name;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile information updated successfully!',
            'new_name' => $full_name // <-- ADD THIS LINE
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to apply changes.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>