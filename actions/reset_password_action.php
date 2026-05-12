<?php
session_start();

// Database connection path remains solid
require_once '../backend/includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // 1. Basic Validation
    if (strlen($password) < 8) {
        $_SESSION['error_reset'] = "Password must be at least 8 characters long.";
        // Use Clean URL for redirect
        header("Location: ../reset_password?token=" . $token);
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['error_reset'] = "Passwords do not match.";
        header("Location: ../reset_password?token=" . $token);
        exit();
    }

    try {
        // 2. Token Verification
        // Note: NOW() works perfectly on Clever Cloud as long as the DB timezone is synced
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // 3. Secure Hashing
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // 4. Update and Securely Clear Tokens
            $update = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
            $update->execute([$hashedPassword, $user['id']]);

            $_SESSION['reset_success'] = true;
            header("Location: ../login"); // Clean URL
            exit();
        } else {
            $_SESSION['error_login'] = "Invalid session or expired link. Please request a new one.";
            header("Location: ../login");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Reset Action Error: " . $e->getMessage());
        $_SESSION['error_reset'] = "A database error occurred.";
        header("Location: ../reset_password?token=" . $token);
        exit();
    }
} else {
    header("Location: ../login");
    exit();
}