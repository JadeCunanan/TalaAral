<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: /dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | TalaAral</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/login.css">
</head>

<body>

    <a href="/" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>

    <div class="split-wrapper">

        <section class="visual-side">
            <div class="visual-depth-card">
                <div class="logo-wrapper">
                    <div class="logo">Tala<span>Aral</span></div>
                    <div class="badge">Academic Workspace - RTU Pasig Branch</div>
                </div>

                <h1>Welcome <br>back, <span class="highlight">Ka-Tala.</span></h1>
                <p>Access your academic tools in one centralized platform. Sign in to continue your journey.</p>

                <div class="mini-features">
                    <div class="m-feat"><i class="fa-solid fa-bolt-lightning"></i> Real-time Academic Updates</div>
                    <div class="m-feat"><i class="fa-solid fa-shield-halved"></i> Secure Student Access</div>
                </div>
            </div>
        </section>

        <section class="form-side">
            <div class="glass-card">
                <div class="form-header">
                    <h2>Sign in.</h2>
                    <p>Please enter your institutional credentials.</p>
                </div>

                <form action="/actions/login_action.php" method="POST">

                    <div class="input-group">
                        <label for="email">Institutional Email</label>
                        <input type="email" id="email" name="email" placeholder="2024-200XXX@rtu.edu.ph" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper" style="position: relative;">
                            <input type="password" id="password" name="password" style="padding-right: 40px;" required>
                            <i class="fa-solid fa-eye-slash" id="togglePass" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8;"></i>
                        </div>
                        <a href="javascript:void(0)" class="forgot-link" onclick="openModal('recoveryModal')">Forgot password?</a>
                    </div>

                    <div class="form-actions" style="margin-top: 24px;">
                        <button type="submit" name="login" class="btn-main">Sign In</button>
                        <p class="signin-link">
                            Don't have an account yet? <a href="/register">Create account</a>
                        </p>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <?php if (isset($_GET['verified']) && $_GET['verified'] === 'true'): ?>
        <div class="modal-overlay active" id="verifiedModal">
            <div class="modal-card">
                <div class="modal-icon success"><i class="fa-solid fa-check"></i></div>
                <h3>Account Verified!</h3>
                <p>Your student account is now active. Sign in below to access your workspace.</p>
                <button class="btn-main" onclick="closeModal('verifiedModal')">Sign In</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="modal-overlay" id="recoveryModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Recover Access</h3>
                <p>Enter your student email to receive a reset password link.</p>
            </div>
            <form action="/actions/login_action.php" method="POST">
                <div class="input-group" style="text-align: left;">
                    <label>Institutional Email</label>
                    <input type="email" name="recovery_email" placeholder="2024-200XXX@rtu.edu.ph" required>
                </div>
                <button type="submit" name="request_reset" class="btn-main">Send Reset Link</button>
                <button type="button" class="btn-ghost" onclick="closeModal('recoveryModal')">Cancel</button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['error_login'])): ?>
        <div class="modal-overlay active" id="loginErrorModal">
            <div class="modal-card">
                <div class="modal-icon error"><i class="fa-solid fa-circle-xmark"></i></div>
                <h3>Login Failed</h3>
                <p><?php echo htmlspecialchars($_SESSION['error_login']);
                    unset($_SESSION['error_login']); ?></p>
                <button class="btn-main" onclick="closeModal('loginErrorModal')">Try Again</button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_recovery'])): ?>
        <div class="modal-overlay active" id="recoveryErrorModal">
            <div class="modal-card">
                <div class="modal-icon error"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h3>Request Failed</h3>
                <p><?php echo htmlspecialchars($_SESSION['error_recovery']);
                    unset($_SESSION['error_recovery']); ?></p>
                <button class="btn-main" onclick="closeModal('recoveryErrorModal')">Close</button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['reset_success'])): ?>
        <div class="modal-overlay active" id="resetSuccessModal">
            <div class="modal-card">
                <div class="modal-icon success"><i class="fa-solid fa-check"></i></div>
                <h3>Password Updated</h3>
                <p>Your password has been successfully updated. You can now log in with your new credentials.</p>
                <button class="btn-main" onclick="closeModal('resetSuccessModal')">Continue to Workspace</button>
            </div>
        </div>
        <?php unset($_SESSION['reset_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_recovery'])): ?>
        <div class="modal-overlay active" id="recoverySuccessModal">
            <div class="modal-card">
                <div class="modal-icon success"><i class="fa-solid fa-circle-check"></i></div>
                <h3>Email Sent</h3>
                <p><?php echo htmlspecialchars($_SESSION['success_recovery']);
                    unset($_SESSION['success_recovery']); ?></p>
                <button class="btn-main" onclick="closeModal('recoverySuccessModal')">Got it</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Password toggle
            const togglePass = document.querySelector('#togglePass');
            const passwordField = document.querySelector('#password');
            if (togglePass && passwordField) {
                togglePass.addEventListener('click', () => {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    togglePass.classList.toggle('fa-eye');
                    togglePass.classList.toggle('fa-eye-slash');
                });
            }

            // Close modal on backdrop click
            document.querySelectorAll('.modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', e => {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });
        });
    </script>
</body>

</html>