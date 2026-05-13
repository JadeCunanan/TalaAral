<?php
session_start();

// 1. DEPENDENCIES
require_once '../backend/includes/db.php';
require_once '../backend/api/send_mail.php';

// Grab the dynamic APP_URL for verification links
$app_url = rtrim(getenv('APP_URL') ?: 'http://localhost', '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    // 1. Capture and Trim Inputs
    $full_name      = trim($_POST['full_name']);
    $student_number = trim($_POST['student_number']);
    $email          = trim($_POST['email']);
    $program_id     = (int) $_POST['program_id'];
    $year_level     = $_POST['year_level'];
    $password       = $_POST['password'];

    // 2. Map program_id → abbreviation
    $program_map = [
        1 => 'BSIT',
        2 => 'BSBA-FM',
        3 => 'BSBA-HRM',
        4 => 'BSBA-MM',
        5 => 'BSBA-OM',
    ];

    if (!array_key_exists($program_id, $program_map)) {
        $_SESSION['error'] = "Invalid program selected.";
        header("Location: ../register"); // Clean URL
        exit();
    }

    $program_abbreviation = $program_map[$program_id];

    // 3. Server-side Validation
    if (!preg_match('/^[0-9]{4}-[0-9]{6}$/', $student_number)) {
        $_SESSION['error'] = "Format must be YYYY-NNNNNN.";
        header("Location: ../register");
        exit();
    }

    if (!str_ends_with($email, "@rtu.edu.ph")) {
        $_SESSION['error'] = "Use your official RTU student email.";
        header("Location: ../register");
        exit();
    }

    try {
        // 4. Duplicate Check
        $check = $pdo->prepare("SELECT id FROM users WHERE student_number = ? OR email = ? LIMIT 1");
        $check->execute([$student_number, $email]);

        if ($check->fetch()) {
            $_SESSION['error'] = "This student number or email is already in use.";
            header("Location: ../register");
            exit();
        }

        // 5. Secure Hashing & Token Generation
        $password_hash      = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));
        $token_expires      = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // 6. Insert to DB
        $sql = "INSERT INTO users 
                    (full_name, student_number, email, password_hash, program_id, program_abbreviation, year_level, verification_token, token_expires, is_verified) 
                VALUES 
                    (:name, :snum, :email, :pass, :prog, :prog_abbr, :yr, :token, :expiry, 0)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'      => $full_name,
            ':snum'      => $student_number,
            ':email'     => $email,
            ':pass'      => $password_hash,
            ':prog'      => $program_id,
            ':prog_abbr' => $program_abbreviation,
            ':yr'        => $year_level,
            ':token'     => $verification_token,
            ':expiry'    => $token_expires,
        ]);

        // 7. Send Verification Email using dynamic APP_URL
        $verification_link = $app_url . "/actions/verify.php?token=" . $verification_token;

        $subject = "Verify Your TalaAral Account";
        $body    = "
            <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; color: #1e293b;'>
                <h2 style='color: #2563eb;'>Welcome to TalaAral, $full_name!</h2>
                <p>Thank you for joining the unified workspace for RTU students. Please click the button below to verify your account.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$verification_link' style='background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Verify Account</a>
                </div>
                <p style='font-size: 0.8rem; color: #94a3b8;'>This link will expire in 24 hours. If you did not create an account, please ignore this email.</p>
            </div>
        ";

        if (sendTalaAralEmail($email, $subject, $body)) {
            $_SESSION['verification_sent']  = true;
            $_SESSION['verification_email'] = $email;
        } else {
            $_SESSION['error'] = "Account created, but we couldn't send the verification email.";
        }

        header("Location: ../register");
        exit();
    } catch (PDOException $e) {
        error_log("Reg Error: " . $e->getMessage());
        $_SESSION['error'] = "System error. Contact admin.";
        header("Location: ../register");
        exit();
    }
}