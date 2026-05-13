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
    <title>TalaAral</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/index.css">
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
                <a href="/login" class="nav-link">Sign In</a>
                <a href="/register" class="btn-nav">
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
                    <a href="/register" class="btn-main">
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
                    <a href="/register" class="btn-white">
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
                                <li><a href="/login">Sign In</a></li>
                                <li><a href="/register">Register</a></li>
                            </ul>
                        </div>
                        <div class="footer-group">
                            <h4>Support</h4>
                            <ul>
                                <li><a href="mailto:talaaral.ph@gmail.com" id="contactLink">Contact Us</a></li>
                                <li><a href="javascript:void(0)" id="openFaqs">FAQs</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="footer-cta">
                        <p class="footer-cta-label">Join the platform</p>
                        <a href="/register" class="footer-btn-primary">
                            <i class="fa-solid fa-user-plus"></i>
                            Create Account
                        </a>
                        <a href="/login" class="footer-btn-secondary">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            Sign In
                        </a>
                    </div>
                </div>

                <div class="footer-bottom">
                    <p>&copy; 2026 TalaAral. All rights reserved.</p>
                    <div class="bottom-links">
                        <a href="javascript:void(0)" id="openPrivacy">Privacy Policy</a>
                        <a href="javascript:void(0)" id="openTerms">Terms of Service</a>
                    </div>
                </div>

            </div>
        </div>
    </footer>

    <div class="modal-overlay" id="faqModal">
        <div class="modal-card content-card">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-family: var(--font-display, 'Momo Trust Display', sans-serif); font-size: 1.5rem; margin: 0; color: var(--navy);">Frequently Asked Questions</h3>
                <button class="btn-close-icon" id="closeFaqTop" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-scroll-content">
                <div class="faq-list">
                    <details class="faq-item">
                        <summary class="faq-question">
                            What is TalaAral?
                            <i class="fa-solid fa-chevron-down"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>TalaAral is a centralized academic platform that helps students organize tasks, deadlines, and learning materials in one place.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-question">
                            Who can use TalaAral?
                            <i class="fa-solid fa-chevron-down"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>TalaAral is designed specifically for students of Rizal Technological University - Pasig Branch to help them manage their academic tasks and responsibilities efficiently.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-question">
                            Why should I use TalaAral?
                            <i class="fa-solid fa-chevron-down"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>TalaAral helps students improve productivity and time management by providing a structured system for tracking tasks, prioritizing responsibilities, and managing academic requirements.</p>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="privacyModal">
        <div class="modal-card content-card">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-family: var(--font-display, 'Momo Trust Display', sans-serif); font-size: 1.5rem; margin: 0; color: var(--navy);">Privacy Policy</h3>
                <button class="btn-close-icon" id="closePrivacyTop" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-scroll-content">
                <p><strong>1. Scope</strong><br>This Privacy Policy explains how TalaAral collects, uses, shares, and protects personal data when you use our web-based academic workspace. By using the platform, you agree to the practices described in this policy.</p>

                <p><strong>2. Data We Collect</strong><br>We collect information necessary to provide a centralized academic experience:</p>
                <ul>
                    <li><strong>Account Data:</strong> Fullname, student number, institutional email, program and year level, and hashed passwords stored in our MySQL database.</li>
                    <li><strong>Academic Data (Canvas LMS Integration):</strong> Through the Canvas LMS API, we fetch course content, modules, and learning materials shared by your instructors.</li>
                    <li><strong>Task Management Data:</strong> Titles, course categories, priority levels, and deadlines for tasks you create within the dashboard.</li>
                    <li><strong>Technical Usage Data:</strong> Device information, log data, and session data processed via PHP to maintain a responsive interface.</li>
                    <li><strong>External Feed Data:</strong> Information retrieved via RSS/SimplePie from official RTU news sources for the Updates module.</li>
                </ul>

                <p><strong>3. How We Use Your Data</strong></p>
                <ul>
                    <li><strong>Service Delivery:</strong> To populate your student dashboard with tasks and visualize your workload via the Task Calendar.</li>
                    <li><strong>Academic Integration:</strong> To sync real-time academic records and materials from Canvas LMS directly into your workspace.</li>
                    <li><strong>Notifications:</strong> To send automated email reminders and real-time alerts regarding upcoming deadlines.</li>
                    <li><strong>Institutional Updates:</strong> To provide a filtered feed of RTU news and announcements alongside your coursework.</li>
                </ul>

                <p><strong>4. Legal Basis and Compliance</strong><br>TalaAral processes data in accordance with the Republic Act No. 10173, also known as the Data Privacy Act of 2012 (DPA). Our processing is based on:</p>
                <ul>
                    <li><strong>Contract Performance:</strong> To provide the workspace features you signed up for.</li>
                    <li><strong>Legitimate Interest:</strong> To improve academic productivity and institutional connectivity for RTU students.</li>
                </ul>

                <p><strong>5. Data Sharing</strong></p>
                <ul>
                    <li><strong>Content Management System:</strong> Courses and modules uploaded in Canvas LMS are fetched directly to TalaAral.</li>
                    <li><strong>Infrastructure Providers:</strong> Data is stored and processed using Dockerized environments hosted on cloud services (Clever Cloud, Render).</li>
                    <li><strong>No Third-Party Sales:</strong> TalaAral does not sell your personal academic data to third-party advertisers.</li>
                </ul>

                <p><strong>6. Security</strong><br>We implement organizational and technical safeguards to protect your information:</p>
                <ul>
                    <li><strong>Encryption:</strong> Use of password hashing and secure API tokens for Canvas LMS integration.</li>
                    <li><strong>Containerization:</strong> Using Docker to ensure environment consistency and minimize security vulnerabilities.</li>
                    <li><strong>Access Control:</strong> PHP session management ensures that only you can view your personal task dashboard.</li>
                </ul>

                <p><strong>7. Your Privacy Rights</strong><br>Under the DPA, you have the right to:</p>
                <ul>
                    <li><strong>Access:</strong> View the academic data and tasks stored in our system.</li>
                    <li><strong>Correction:</strong> Update or modify task titles, courses, and deadlines.</li>
                    <li><strong>Object:</strong> Limit the processing of your data, such as opting out of automated email notifications.</li>
                </ul>

                <p><strong>8. Policy Updates</strong><br>TalaAral may update this policy to reflect changes in our features (such as new API integrations). Material changes will be notified via the Student Dashboard.</p>

                <p><strong>10. Contact Us</strong><br>For concerns regarding these Privacy Policy, users may contact the TalaAral support team through our official email at <a href="mailto:talaaral.ph@gmail.com" style="color: var(--navy-bright, #0d6efd); text-decoration: none; font-weight: 600;">talaaral.ph@gmail.com</a>.</p>
            </div>

            <div style="margin-top: 24px; text-align: right;">
                <button class="btn-main" id="closePrivacyBtn">Understood</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="termsModal">
        <div class="modal-card content-card">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-family: var(--font-display, 'Momo Trust Display', sans-serif); font-size: 1.5rem; margin: 0; color: var(--navy);">Terms of Service</h3>
                <button class="btn-close-icon" id="closeTermsTop" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-scroll-content">
                <p>Welcome to TalaAral.<br>By accessing and using TalaAral, you agree to comply with and be bound by the following Terms of Service. Please read them carefully.</p>

                <p><strong>1. Acceptance of Terms</strong><br>By creating an account and using TalaAral, you confirm that you are a student of Rizal Technological University – Pasig Branch and that you agree to follow these Terms of Service.</p>

                <p><strong>2. Description of Service</strong><br>TalaAral is a centralized academic platform designed to help students organize academic tasks, deadlines, course materials, announcements, and schedules in one place. The platform aims to improve productivity, communication, and academic management for students.</p>

                <p><strong>3. User Responsibilities</strong><br>Users agree to:</p>
                <ul>
                    <li>Provide accurate and truthful information during registration.</li>
                    <li>Keep login credentials secure and confidential.</li>
                    <li>Use the platform only for academic and educational purposes.</li>
                </ul>

                <p><strong>4. Account Security</strong><br>Users are responsible for maintaining the confidentiality of their account credentials. TalaAral is not liable for unauthorized access caused by the user’s failure to protect their login information.</p>

                <p><strong>5. Privacy and Data Protection</strong><br>TalaAral collects only necessary academic and account-related information to provide its services. User data will be handled in accordance with applicable privacy policies and institutional data protection standards. Personal information will not be shared without user consent unless required by law.</p>

                <p><strong>6. System Availability</strong><br>While TalaAral aims to provide reliable service, uninterrupted access cannot be guaranteed at all times due to maintenance, updates, or technical issues. The platform reserves the right to temporarily suspend services when necessary.</p>

                <p><strong>7. Intellectual Property</strong><br>All system content, design, features, and materials within TalaAral remain the property of the developers unless otherwise stated. Users may not copy, reproduce, or distribute platform materials without proper permission.</p>

                <p><strong>8. Termination of Access</strong><br>TalaAral reserves the right to suspend or terminate user access for violations of these Terms of Service, misuse of the platform, or activities that threaten system security.</p>

                <p><strong>9. Limitation of Liability</strong><br>TalaAral serves as an academic support platform and does not replace official university systems. The platform is not responsible for academic consequences resulting from missed deadlines, incorrect uploads, or user negligence.</p>

                <p><strong>10. Changes to Terms</strong><br>These Terms of Service may be updated as the platform develops and new features are added. Users will be notified of significant changes through the Student Dashboard or via email.</p>

                <p><strong>11. Contact Information</strong><br>For concerns regarding these Terms of Service, users may contact the TalaAral support team through our official email at <a href="mailto:talaaral.ph@gmail.com" style="color: var(--navy-bright, #0d6efd); text-decoration: none; font-weight: 600;">talaaral.ph@gmail.com</a>.</p>

                <p style="margin-top: 24px; font-style: italic; color: var(--text-muted);">By continuing to use TalaAral, you acknowledge that you have read, understood, and agreed to these Terms of Service.</p>
            </div>

            <div style="margin-top: 24px; text-align: right;">
                <button class="btn-main" id="closeTermsBtn">I Agree</button>
            </div>
        </div>
    </div>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('active');
            });
        }, {
            threshold: 0.08
        });
        document.querySelectorAll('.reveal-up, .reveal-fade').forEach(el => observer.observe(el));

        document.addEventListener('DOMContentLoaded', () => {

            // --- Smart Mailto Link (Copies to clipboard) ---
            const contactLink = document.getElementById('contactLink');
            if (contactLink) {
                contactLink.addEventListener('click', (e) => {
                    navigator.clipboard.writeText('talaaral.ph@gmail.com').then(() => {
                        const originalText = contactLink.innerHTML;
                        contactLink.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                        contactLink.style.color = 'var(--gold, #f5c842)';

                        setTimeout(() => {
                            contactLink.innerHTML = originalText;
                            contactLink.style.color = '';
                        }, 2500);
                    });
                });
            }

            // --- Unified Modal Logic ---
            const modals = {
                privacy: {
                    overlay: document.getElementById('privacyModal'),
                    triggers: [document.getElementById('openPrivacy')],
                    closers: [document.getElementById('closePrivacyTop'), document.getElementById('closePrivacyBtn')]
                },
                faq: {
                    overlay: document.getElementById('faqModal'),
                    triggers: [document.getElementById('openFaqs')],
                    closers: [document.getElementById('closeFaqTop')]
                },
                terms: {
                    overlay: document.getElementById('termsModal'),
                    triggers: [document.getElementById('openTerms')],
                    closers: [document.getElementById('closeTermsTop'), document.getElementById('closeTermsBtn')]
                }
            };

            const closeAllModals = () => {
                Object.values(modals).forEach(m => {
                    if (m.overlay) m.overlay.classList.remove('active');
                });
                document.body.style.overflow = '';
            };

            Object.values(modals).forEach(m => {
                if (!m.overlay) return;

                // Open Triggers
                m.triggers.forEach(trigger => {
                    if (trigger) {
                        trigger.addEventListener('click', (e) => {
                            e.preventDefault();
                            closeAllModals(); 
                            m.overlay.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        });
                    }
                });

                // Close Triggers
                m.closers.forEach(closer => {
                    if (closer) closer.addEventListener('click', closeAllModals);
                });

                // Click Outside
                m.overlay.addEventListener('click', (e) => {
                    if (e.target === m.overlay) closeAllModals();
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeAllModals();
            });

            // FAQ Accordion Animation Logic
            const faqs = document.querySelectorAll('.faq-item');
            faqs.forEach(faq => {
                faq.addEventListener('click', (e) => {
                    if (e.target.tagName !== 'SUMMARY' && !e.target.closest('summary')) return;

                    faqs.forEach(item => {
                        if (item !== faq) item.removeAttribute('open');
                    });
                });
            });
        });
    </script>
</body>

</html>