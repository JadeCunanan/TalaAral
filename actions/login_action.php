<?php
session_start();

// 1. DEPENDENCIES
require_once '../backend/includes/db.php';
require_once '../backend/api/send_mail.php';

// Grab the dynamic APP_URL for recovery links
$app_url = rtrim(getenv('APP_URL') ?: 'http://localhost', '/');

// --- SECTION 1: PRIMARY LOGIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error_login'] = "Please enter both your student email and password.";
        header("Location: ../login"); // Updated to use Clean URL
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            session_regenerate_id(true);

            $_SESSION['user_id']               = $user['id'];
            $_SESSION['full_name']             = $user['full_name'];
            $_SESSION['program_id']            = $user['program_id'];
            $_SESSION['program_abbreviation'] = $user['program_abbreviation'] ?? 'RTU Scholar';
            $_SESSION['year_level']           = $user['year_level'] ?? '';
            $_SESSION['profile_pic']          = $user['profile_pic'] ?? '';

            // Redirect to the clean URL handled by your .htaccess
            header("Location: ../dashboard"); 
            exit();

        } else {
            $_SESSION['error_login'] = "Invalid credentials or account not yet verified.";
            header("Location: ../login");
            exit();
        }

    } catch (PDOException $e) {
        error_log("TalaAral Login Error: " . $e->getMessage());
        $_SESSION['error_login'] = "A system error occurred.";
        header("Location: ../login");
        exit();
    }
}

// --- RECOVERY LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = filter_var($_POST['recovery_email'] ?? '', FILTER_SANITIZE_EMAIL);

    try {
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ? AND is_verified = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token  = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

            $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?")
                ->execute([$token, $expiry, $email]);

            // Use the APP_URL variable for a perfect link every time
            $resetLink = $app_url . "/reset_password?token=" . $token;

            $subject = "TalaAral Account Recovery";
            $body    = "
            <div style='font-family: sans-serif; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; color: #1e293b;'>
                <h2 style='color: #0f172a;'>Reset Your Password</h2>
                <p>Hi " . htmlspecialchars($user['full_name']) . ",</p>
                <p>Click the button below to secure your account. This link expires in 1 hour.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$resetLink' style='background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;'>Reset Password</a>
                </div>
                <p style='font-size: 12px; color: #64748b;'>If you didn't request this, you can safely ignore this email.</p>
            </div>";

            if (sendTalaAralEmail($email, $subject, $body)) {
                $_SESSION['success_recovery'] = "A secure link has been sent to your inbox.";
            } else {
                $_SESSION['error_recovery'] = "Mail delivery failed.";
            }
        } else {
            $_SESSION['error_recovery'] = "No verified account found with that email.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_recovery'] = "Database error. Please try again.";
    }

    header("Location: ../login");
    exit();
}