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
    <title>Join TalaAral Now</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/register.css">

    <style>
        /* ── Custom Checkbox Fix ── */
        .check-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            margin-bottom: 24px;
            user-select: none;
            padding: 16px;
            border-radius: 12px;
            border: 1.5px solid var(--input-border);
            background-color: var(--bg-subtle);
            transition: border-color 0.2s, background 0.2s;
        }

        .check-box:hover {
            border-color: var(--accent-blue);
            background-color: var(--accent-light);
        }

        .check-box input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .custom-check {
            width: 22px;
            height: 22px;
            min-width: 22px;
            border-radius: 6px;
            border: 2px solid var(--input-border);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .custom-check i {
            font-size: 0.75rem;
            color: #fff;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.15s ease;
        }

        .check-box input[type="checkbox"]:checked~.custom-check {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .check-box input[type="checkbox"]:checked~.custom-check i {
            opacity: 1;
            transform: scale(1);
        }

        .check-box:has(input:checked) {
            border-color: var(--accent-blue);
            background-color: var(--accent-light);
        }

        .check-box .text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .check-box .text a {
            color: var(--accent-blue);
            font-weight: 600;
            text-decoration: underline;
            padding: 4px 0;
        }
    </style>
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
                    <div class="badge">Your Unified Academic Workspace</div>
                </div>

                <h1>Empower your <br>academic <span class="highlight">potential.</span></h1>
                <p>The unified workspace for RTU Pasig students. Designed for efficiency, built for you.</p>

                <div class="mini-features">
                    <div class="m-feat"><i class="fa-solid fa-check"></i> Canvas LMS sync</div>
                    <div class="m-feat"><i class="fa-solid fa-check"></i> Learning Resources</div>
                </div>
            </div>
        </section>

        <section class="form-side">
            <div class="glass-card">
                <div class="form-header">
                    <h2>Create your account.</h2>
                    <p>Enter your student details to get started.</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert error">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <?php echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert success">
                        <i class="fa-solid fa-circle-check"></i>
                        <?php echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="/actions/register_action.php" method="POST" class="auth-grid">

                    <div class="input-group">
                        <label for="full_name">Full name</label>
                        <input type="text" id="full_name" name="full_name" placeholder="e.g. Cunanan, Jade A." required>
                    </div>

                    <div class="input-row-flex">
                        <div class="input-group">
                            <label for="student_number">Student Number</label>
                            <input type="text" id="student_number" name="student_number" placeholder="2024-200XXX" pattern="[0-9]{4}-[0-9]{6}" title="Format: YYYY-NNNNNN" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Institutional Email</label>
                            <input type="email" id="email" name="email" placeholder="@rtu.edu.ph" required>
                        </div>
                    </div>

                    <div class="input-row-flex">
                        <div class="input-group">
                            <label for="program_id">Program</label>
                            <select id="program_id" name="program_id" class="custom-select" required>
                                <option value="" disabled selected>Select Program</option>
                                <option value="1">BSIT</option>
                                <option value="2">BSBA-FM</option>
                                <option value="3">BSBA-HRM</option>
                                <option value="4">BSBA-MM</option>
                                <option value="5">BSBA-OM</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="year_level">Year Level</label>
                            <select id="year_level" name="year_level" class="custom-select" required>
                                <option value="" disabled selected>Select Level</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper" style="position: relative;">
                            <input type="password" id="password" name="password" minlength="8" style="padding-right: 48px;" required>
                            <i class="fa-solid fa-eye-slash" id="togglePass" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; padding: 10px;"></i>
                        </div>
                    </div>

                    <label class="check-box" for="terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        <span class="custom-check">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span class="text">
                            I have read and agree to the <a href="javascript:void(0)" id="openTerms">Terms & Conditions</a>
                        </span>
                    </label>

                    <div class="form-actions">
                        <button type="submit" name="register" class="btn-main">Create account</button>
                        <p class="signin-link">
                            Already have an account? <a href="/login">Sign in</a>
                        </p>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <?php if (isset($_SESSION['verification_sent'])): ?>
        <div class="modal-overlay active" id="verifyModal">
            <div class="modal-card">
                <div class="modal-icon email"><i class="fa-regular fa-envelope"></i></div>
                <h3>Check your inbox</h3>
                <p>A verification link has been sent to <br><span class="email-highlight"><?php echo htmlspecialchars($_SESSION['verification_email']); ?></span>.<br>Please click the link to activate your account.</p>
                <button class="btn-main" onclick="document.getElementById('verifyModal').classList.remove('active')">Got it</button>
            </div>
        </div>
    <?php unset($_SESSION['verification_sent']);
        unset($_SESSION['verification_email']);
    endif; ?>

    <?php if (isset($_GET['verified']) && $_GET['verified'] == 'true'): ?>
        <div class="modal-overlay active" id="successModal">
            <div class="modal-card">
                <div class="modal-icon success"><i class="fa-solid fa-check"></i></div>
                <h3>Account Verified!</h3>
                <p>Your student account has been successfully created. You can now access your workspace.</p>
                <a href="/login" style="text-decoration: none;">
                    <button class="btn-main" style="background: #22c55e;">Login Now</button>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="modal-overlay" id="termsModal">
        <div class="modal-card terms-card">
            <h3>Terms & Conditions</h3>

            <div class="terms-content">
                <p><strong>1. Introduction</strong><br>Welcome to TalaAral. By creating and using an account, you agree to follow the rules of this academic workspace designed for RTU Pasig students.</p>
                <p><strong>2. Academic Integrity</strong><br>Users are expected to use TalaAral responsibly and in line with academic standards. The platform is intended to support learning and personal academic development.</p>
                <p><strong>3. User Accounts</strong><br>Users are responsible for keeping their login credentials secure. Account sharing is not allowed. TalaAral may suspend accounts involved in suspicious or unauthorized activity.</p>
                <p><strong>4. Acceptable Use & Conduct</strong><br>TalaAral is designed for educational and personal academic use. Any misuse of the system, including actions that disrupt functionality or compromise system stability, may result in account restriction or suspension.</p>
                <p><strong>5. Data Privacy</strong><br>We respect your privacy. Collected information (such as student email, account details, and academic data) is used only for verification, system access, and notifications in compliance with the Data Privacy Act of 2012.</p>
                <p><strong>6. Content & Ownership</strong><br>Users retain ownership of their submitted work. Faculty retain ownership of uploaded academic materials. TalaAral stores content only for system functionality and does not claim ownership of user content.</p>
                <p><strong>7. System Availability & Liability</strong><br>TalaAral aims to remain accessible at all times but may experience downtime due to maintenance or technical issues. The system is not liable for data loss or access interruptions.</p>
                <p><strong>8. Account Suspension</strong><br>Accounts may be suspended or terminated for violating rules, engaging in harmful behavior, or compromising system security.</p>
                <p><strong>9. Updates to Terms</strong><br>These Terms & Conditions may be updated as TalaAral continues to improve and develop. Continued use of the platform means acceptance of any changes.</p>
                <p><strong>10. Compliance</strong><br>TalaAral follows applicable institutional and academic guidelines within RTU Pasig.</p>
            </div>

            <button class="btn-main" id="closeTerms">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Password Toggle
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

            // Student Number Formatting (YYYY-NNNNNN)
            const studentNumInput = document.getElementById('student_number');
            if (studentNumInput) {
                studentNumInput.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/[^0-9-]/g, '');
                    const parts = value.split('-');
                    if (parts.length > 2) value = parts[0] + '-' + parts.slice(1).join('');
                    if (!value.includes('-') && value.length > 4) value = value.slice(0, 4) + '-' + value.slice(4, 10);
                    if (value.length > 11) value = value.slice(0, 11);
                    e.target.value = value;
                });
            }

            // Terms Modal Logic
            const openTerms = document.getElementById('openTerms');
            const closeTerms = document.getElementById('closeTerms');
            const termsModal = document.getElementById('termsModal');
            if (openTerms && termsModal) openTerms.addEventListener('click', () => termsModal.classList.add('active'));
            if (closeTerms && termsModal) closeTerms.addEventListener('click', () => termsModal.classList.remove('active'));

            // Auto-hide Alerts
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 500);
                }, 4000);
            });
        });
    </script>
</body>

</html>