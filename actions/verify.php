<?php
session_start();
require_once '../backend/includes/db.php';

if (isset($_GET['token'])) {
    $token        = $_GET['token'];
    $current_time = date('Y-m-d H:i:s');

    // Build base URL dynamically — works on localhost and live servers
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'];

    try {
        // 1. Find the user with this token who isn't verified yet
        $stmt = $pdo->prepare("SELECT id, token_expires FROM users WHERE verification_token = ? AND is_verified = 0 LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // 2. Check if the token has expired (24-hour window)
            if ($current_time > $user['token_expires']) {
                $_SESSION['error'] = "This verification link has expired. Please register again.";

                // Delete the unverified user so they can re-register
                $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $del->execute([$user['id']]);

                header("Location: " . $base_url . "/register.php");
                exit();
            }

            // 3. Token is valid — mark as verified and clear the token
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, token_expires = NULL WHERE id = ?");
            $update->execute([$user['id']]);

            // 4. Redirect to login page with verified flag to trigger success modal
            header("Location: " . $base_url . "/login.php?verified=true");
            exit();

        } else {
            // Token not found or already verified
            $_SESSION['error'] = "Invalid or expired verification link.";
            header("Location: " . $base_url . "/register.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Verification Error: " . $e->getMessage());
        $_SESSION['error'] = "A system error occurred. Please try again later.";
        header("Location: " . $base_url . "/register.php");
        exit();
    }

} else {
    // Accessing verify.php without a token
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'];
    header("Location: " . $base_url . "/register.php");
    exit();
}