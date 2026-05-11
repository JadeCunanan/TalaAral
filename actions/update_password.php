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
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// 2. Input Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'error' => 'All password fields are required.']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'error' => 'Your new passwords do not match.']);
    exit();
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'error' => 'New password must be at least 8 characters long.']);
    exit();
}

try {
    if (!isset($pdo)) throw new Exception("Database connection failed.");

    // 3. Fetch the user's current password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User account not found.']);
        exit();
    }

    // 4. Verify that they typed their current password correctly
    if (!password_verify($current_password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'error' => 'Incorrect current password.']);
        exit();
    }

    // 5. Hash the new password and update the database
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $updateStmt->execute([$new_hash, $user_id]);

    echo json_encode([
        'success' => true, 
        'message' => 'Password securely updated!'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>