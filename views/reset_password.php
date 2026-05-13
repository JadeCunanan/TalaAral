<?php
session_start();
// Security: Redirect if no token is present
if (!isset($_GET['token'])) {
    header("Location: /login");
    exit();
}

require_once '../backend/includes/db.php';

$token = $_GET['token'];
$user_data = null;

try {
    // Validate token
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user_data = $stmt->fetch();
} catch (PDOException $e) {
    error_log("TalaAral Recovery Error: " . $e->getMessage());
}

// Redirect if the link is invalid or expired
if (!$user_data) {
    $_SESSION['error_login'] = "The recovery link is invalid or has expired.";
    header("Location: /login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password | TalaAral</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/login.css">

    <style>
        body {
            background-color: var(--bg-visual);
            background-image:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.05), transparent 40%),
                radial-gradient(circle at bottom right, rgba(181, 148, 0, 0.05), transparent 40%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .recovery-container {
            width: 100%;
            max-width: 460px;
            animation: slideUpFade 0.8s var(--bounce);
        }

        .header-brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header-brand .logo {
            font-family: var(--font-display);
            font-size: 2.5rem;
        }

        .header-brand .logo span {
            color: var(--accent-blue);
        }

        .premium-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 3rem;
            border-radius: 32px;
            box-shadow: var(--shadow-lg);
        }

        /* Fixed Eye-Icon Placement */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 45px;
            /* Space for the icon */
        }

        .toggle-icon {
            position: absolute;
            right: 16px;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s;
            z-index: 10;
        }

        .toggle-icon:hover {
            color: var(--accent-blue);
        }

        .validation-alert {
            display: none;
            background: #fff7ed;
            color: #c2410c;
            padding: 14px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 24px;
            border: 1px solid #ffedd5;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="recovery-container">
        <div class="header-brand">
            <div class="logo">Tala<span>Aral</span></div>
        </div>

        <div class="premium-card">
            <div class="form-header" style="text-align: center; margin-bottom: 32px;">
                <h2 style="font-family: var(--font-display); font-size: 2rem;">New Password.</h2>
                <p style="color: var(--text-secondary); font-size: 0.95rem; margin-top: 8px;">
                    Secure access for <span style="color: var(--rtu-gold); font-weight: 700;"><?php echo htmlspecialchars($user_data['full_name']); ?></span>
                </p>
            </div>

            <div id="error-box" class="validation-alert"></div>

            <form action="/actions/reset_password_action.php" method="POST" onsubmit="return validatePasswords()">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="input-group">
                    <label>New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="p1" name="password" required>
                        <i class="fa-solid fa-eye-slash toggle-icon" onclick="toggle('p1', this)"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="p2" name="confirm_password" required>
                        <i class="fa-solid fa-eye-slash toggle-icon" onclick="toggle('p2', this)"></i>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 32px;">
                    <button type="submit" name="update_password" class="btn-main">Update Password</button>
                    <p class="signin-link" style="margin-top: 24px;">
                        Remembered it? <a href="/login">Back to Sign in</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggle(targetId, icon) {
            const field = document.getElementById(targetId);
            const isPassword = field.getAttribute('type') === 'password';
            field.setAttribute('type', isPassword ? 'text' : 'password');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        function validatePasswords() {
            const p1 = document.getElementById('p1').value;
            const p2 = document.getElementById('p2').value;
            const errorBox = document.getElementById('error-box');

            if (p1.length < 8) {
                errorBox.style.display = 'block';
                errorBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Password must be at least 8 characters.';
                return false;
            }

            if (p1 !== p2) {
                errorBox.style.display = 'block';
                errorBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Passwords do not match.';
                return false;
            }

            return true;
        }
    </script>

</body>

</html>