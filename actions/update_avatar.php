<?php
session_start();
// Suppress errors to ensure a clean JSON output
error_reporting(0);
header('Content-Type: application/json');

require_once '../backend/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];
// The frontend will send the cropped image as a Base64 text string (data:image/png;base64,...)
$base64_image = $_POST['avatar_base64'] ?? '';

if (empty($base64_image)) {
    echo json_encode(['success' => false, 'error' => 'No image data received.']);
    exit();
}

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection failed.");
    }
    
    // 1. Data Integrity Check
    // Ensures the string actually starts with a data URI scheme
    if (strpos($base64_image, 'data:image/') !== 0) {
         throw new Exception("Invalid image format.");
    }

    // 2. Save the Base64 string directly into the database
    // This bypasses Render's file deletion issue entirely.
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    
    if ($stmt->execute([$base64_image, $user_id])) {
        
        // 3. Sync Session Memory
        // This ensures the sidebar/topbar updates without needing a re-login
        $_SESSION['profile_pic'] = $base64_image;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated successfully.',
            'new_avatar' => $base64_image
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save to database.']);
    }
    
} catch (Exception $e) {
    error_log("TalaAral Avatar Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'System error occurred.']);
}
?>