<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalaAral</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>

    <div class="flare-bg" aria-hidden="true">
        <div class="flare orb-navy"></div>
        <div class="flare orb-gold"></div>
    </div>

    <nav class="navbar reveal-fade">
        <div class="nav-container">
            <div class="logo">Tala<span>Aral</span></div>
            <div class="nav-right">
                <a href="login.php" class="nav-link">Sign In</a>
                <a href="register.php" class="btn-nav">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span class="btn-text">Get Started</span>
                </a>
            </div>
        </div>
    </nav>

    <main>

        <header class="hero-center">
            <div class="container">
                <div class="reveal-up" style="transition-delay:0.05s;">
                    <span class="badge-pill">RTU Pasig &mdash; Integrated Academic Management System</span>
                </div>
                <h1 class="reveal-up" style="transition-delay:0.15s;">
                    Your Academic Journey,<br>
                    <em class="text-gradient">Synchronized.</em>
                </h1>
                <p class="reveal-up" style="transition-delay:0.25s;">
                    One unified workspace for your tasks, modules, and campus updates &mdash; designed for RTU Pasig students.
                </p>
                <div class="hero-actions reveal-up" style="transition-delay:0.35s;">
                    <a href="register.php" class="btn-main">
                        Create Account
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="#features" class="btn-sub">
                        Explore Features
                        <i class="fa-solid fa-chevron-down"></i>
                    </a>
                </div>
            </div>
        </header>

        <section id="features" class="features">
            <div class="container">

                <div class="section-header reveal-up">
                    <span class="section-eyebrow">Platform Features</span>
                    <h2>Everything you need,<br>in one place.</h2>
                </div>

                <div class="bento-grid">

                    <div class="bento-item bento-task accent-blue reveal-up" style="transition-delay:0.05s;">
                        <div class="bento-deco"></div>
                        <div class="bento-feature-img">
                            <img src="/assets/images/dashboard.png" alt="Centralized Dashboard preview">
                        </div>
                        <div class="bento-info">
                            <div class="bento-title-row">
                                <div class="icon-box blue">
                                    <i class="fa-solid fa-gauge-high"></i>
                                </div>
                                <h3>Centralized Dashboard</h3>
                            </div>
                            <p>Everything at a glance — your tasks, updates, and academic tools, all in one place.</p>
                        </div>
                    </div>

                    <div class="bento-item bento-canvas accent-blue reveal-up" style="transition-delay:0.1s;">
                        <div class="bento-feature-img">
                            <img src="/assets/images/tasks.png" alt="Task Management preview">
                        </div>
                        <div class="bento-info">
                            <div class="bento-title-row">
                                <div class="icon-box blue">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <h3>Task Management</h3>
                            </div>
                            <p>Organize tasks, track deadlines, and manage your academic workload.</p>
                        </div>
                    </div>

                    <div class="bento-item bento-updates accent-gold reveal-up" style="transition-delay:0.15s;">
                        <div class="bento-feature-img">
                            <img src="/assets/images/resources.png" alt="Canvas LMS Resources preview">
                        </div>
                        <div class="bento-info">
                            <div class="bento-title-row">
                                <div class="icon-box gold">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <h3>Canvas LMS Resources</h3>
                            </div>
                            <p>Access your courses and learning materials through a Canvas-powered system.</p>
                        </div>
                    </div>

                    <div class="bento-item bento-resources accent-gold reveal-up" style="transition-delay:0.2s;">
                        <div class="bento-feature-img">
                            <img src="/assets/images/news.png" alt="Campus News Updates preview">
                        </div>
                        <div class="bento-info">
                            <div class="bento-title-row">
                                <div class="icon-box gold">
                                    <i class="fa-solid fa-newspaper"></i>
                                </div>
                                <h3>Campus News Updates</h3>
                            </div>
                            <p>Real-time university news delivered straight to your dashboard.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class="cta-banner reveal-up">
            <div class="container">
                <div class="banner-box">
                    <h2>Ready to be more productive?</h2>
                    <p>Join the TalaAral student community and take control of your academic journey today.</p>
                    <a href="register.php" class="btn-white">
                        Register Now
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
        <div class="footer-accent-bar"></div>
        <div class="footer-inner">
            <div class="container">

                <div class="footer-main">
                    <div class="footer-brand">
                        <div class="footer-logo">Tala<span>Aral</span></div>
                        <p class="footer-tagline">Precision academic management for RTU Pasig students.</p>
                        <div class="social-row">
                            <a href="https://www.facebook.com/profile.php?id=61588896587840" target="_blank" class="social-pill" aria-label="Facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                                Facebook
                            </a>
                        </div>
                    </div>

                    <div class="footer-links">
                        <div class="footer-group">
                            <h4>Platform</h4>
                            <ul>
                                <li><a href="#features">Features</a></li>
                                <li><a href="login.php">Sign In</a></li>
                                <li><a href="register.php">Register</a></li>
                            </ul>
                        </div>
                        <div class="footer-group">
                            <h4>Support</h4>
                            <ul>
                                <li><a href="mailto:talaaral.ph@gmail.com">Contact Us</a></li>
                                <li><a href="#">FAQs</a></li>   
                            </ul>
                        </div>
                    </div>

                    <div class="footer-cta">
                        <p class="footer-cta-label">Join the platform</p>
                        <a href="register.php" class="footer-btn-primary">
                            <i class="fa-solid fa-user-plus"></i>
                            Create Account
                        </a>
                        <a href="login.php" class="footer-btn-secondary">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            Sign In
                        </a>
                    </div>
                </div>

                <div class="footer-bottom">
                    <p>&copy; 2026 TalaAral. All rights reserved.</p>
                    <div class="bottom-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                </div>

            </div>
        </div>
    </footer>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('active');
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('.reveal-up, .reveal-fade').forEach(el => observer.observe(el));
    </script>
</body>

</html>