<?php
session_start();
// Database connection - path goes up one level to find the backend
require_once '../backend/includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // 1. Basic Validation
    if (strlen($password) < 8) {
        $_SESSION['error_reset'] = "Password must be at least 8 characters long.";
        header("Location: ../views/reset_password.php?token=" . $token);
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['error_reset'] = "Passwords do not match.";
        header("Location: ../views/reset_password.php?token=" . $token);
        exit();
    }

    try {
        // 2. Final verification that the token is still valid
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // 3. Secure Hashing: Standard for IT systems integration
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // 4. Update the database and NULL out the tokens for security
            $update = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
            $update->execute([$hashedPassword, $user['id']]);

            $_SESSION['reset_success'] = true;
            header("Location: ../login.php");
            exit();
        } else {
            $_SESSION['error_login'] = "Invalid session. Please request a new link.";
            header("Location: ../login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Reset Action Error: " . $e->getMessage());
        $_SESSION['error_reset'] = "A database error occurred.";
        header("Location: ../views/reset_password.php?token=" . $token);
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}