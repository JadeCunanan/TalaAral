<?php
session_start();
error_reporting(0);
header('Content-Type: application/json');

require_once '../backend/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
    exit();
}

$user_id = $_SESSION['user_id'];
// The frontend will send the cropped image as a Base64 text string
$base64_image = $_POST['avatar_base64'] ?? '';

if (empty($base64_image)) {
    echo json_encode(['success' => false, 'error' => 'No image data received.']);
    exit();
}

try {
    if (!isset($pdo)) throw new Exception("Database connection failed.");
    
    // Save the Base64 string directly into the database
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    
    // Execute and check if successful
    if ($stmt->execute([$base64_image, $user_id])) {
        
        // --- THE CRITICAL UPDATE ---
        // Sync the session memory so the sidebar and topbar update globally instantly
        $_SESSION['profile_pic'] = $base64_image;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated successfully.',
            'new_path' => $base64_image
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save picture to the database.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database Error: ' . $e->getMessage()]);
}
?>