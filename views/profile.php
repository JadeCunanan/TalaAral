<?php
session_start();
require_once '../backend/includes/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT full_name, email, student_number, program_abbreviation, major, year_level, profile_pic, created_at FROM users WHERE id = ?";

try {
    if (!isset($pdo)) throw new Exception("Database connection variable not found.");
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $user = [
            'full_name' => 'Registered User', 'email' => '', 'student_number' => 'N/A',
            'program_abbreviation' => '', 'major' => '', 'year_level' => '', 'profile_pic' => null, 'created_at' => date('Y-m-d H:i:s')
        ];
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$display_name = htmlspecialchars($user['full_name']);
$display_email = htmlspecialchars($user['email']);
$student_no = htmlspecialchars($user['student_number'] ?? 'Unassigned');
$program = htmlspecialchars($user['program_abbreviation'] ?? 'BSIT');
$year = htmlspecialchars($user['year_level'] ?? 'N/A');
$join_date = !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'Recently';
$db_pic = $user['profile_pic'] ?? '';
$has_avatar = !empty($db_pic) && strpos($db_pic, 'data:image') === 0;
$profile_pic_path = $has_avatar ? $db_pic : '';
$initial = !empty($display_name) ? strtoupper(substr(trim($display_name), 0, 1)) : '?';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | TalaAral</title>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">

    <style>
        .input-locked {
            background-color: rgba(100, 116, 139, 0.05) !important;
            color: var(--text-secondary) !important;
            cursor: not-allowed;
            border-color: var(--border-color) !important;
        }
        .input-locked:focus { box-shadow: none !important; border-color: var(--border-color) !important; }
    </style>
</head>
<body>

    <div class="flare-bg"><div class="flare orb-navy"></div><div class="flare orb-gold"></div></div>

    <div class="app-shell">
        <?php include 'components/sidebar.php'; ?>

        <div class="main-wrap">
            <header class="topbar" style="justify-content: space-between;">
                <button class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-left"></div> 
                <div class="topbar-right">
                    <a href="/profile" class="topbar-avatar" id="topbarAvatar">
                        <?php if ($has_avatar): ?>
                            <img src="<?php echo $profile_pic_path; ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; border-radius: 50%; background: var(--navy-bright); color: #fff; display: flex; align-items: center; justify-content: center; font-family: var(--font-title); font-size: 1.2rem;"><?php echo $initial; ?></div>
                        <?php endif; ?>
                    </a>
                </div>
            </header>

            <main class="content">
                <div class="profile-header reveal-up">
                    <div class="welcome-text">
                        <h1 style="font-family: 'Momo Trust Display', sans-serif; font-size: 2.2rem; margin-bottom: 8px;">
                            Account <span class="text-gradient">Settings</span>
                        </h1>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage your academic identity and security preferences.</p>
                    </div>
                </div>

                <div class="profile-grid reveal-up" style="transition-delay: 0.1s;">
                    
                    <div class="left-column-stack">
                        <div class="glass-card identity-card">
                            <label for="avatarInput" class="avatar-editor-wrap" title="Click to select a new picture">
                                <div class="profile-avatar-large" id="avatarPreview">
                                    <?php if ($has_avatar): ?>
                                        <img src="<?php echo $profile_pic_path; ?>" alt="User Avatar">
                                    <?php else: ?>
                                        <span class="avatar-initial"><?php echo $initial; ?></span>
                                    <?php endif; ?>
                                    <div class="edit-overlay"><i class="fa-solid fa-camera"></i><span>Edit Picture</span></div>
                                </div>
                            </label>
                            <input type="file" id="avatarInput" accept="image/png, image/jpeg, image/webp" style="display: none;" />
                            
                            <h2 class="user-title" style="margin-bottom: 0;"><?php echo $display_name; ?></h2>

                            <div class="profile-details-list">
                                <div class="pd-item">
                                    <div class="pd-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                                    <div class="pd-text">
                                        <span class="pd-label">Program</span>
                                        <span class="pd-value"><?php echo $program; ?></span>
                                    </div>
                                </div>
                                <div class="pd-item">
                                    <div class="pd-icon"><i class="fa-solid fa-layer-group"></i></div>
                                    <div class="pd-text">
                                        <span class="pd-label">Year Level</span>
                                        <span class="pd-value"><?php echo $year; ?></span>
                                    </div>
                                </div>
                                <div class="pd-item">
                                    <div class="pd-icon"><i class="fa-regular fa-calendar-check"></i></div>
                                    <div class="pd-text">
                                        <span class="pd-label">Account Created</span>
                                        <span class="pd-value"><?php echo $join_date; ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="glass-card settings-content-card">
                        
                        <section class="settings-section">
                            <div class="card-head"><h2 class="card-title"><i class="fa-solid fa-user-pen"></i> User Information</h2></div>
                            <form id="profileForm" class="quick-form">
                                <div class="qf-row">
                                    <div>
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" value="<?php echo $display_name; ?>" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Email Address <span style="text-transform:none; font-weight:normal; color:var(--text-secondary); font-size: 0.65rem; margin-left: 4px;">(RTU Issued)</span></label>
                                        <input type="email" name="email" value="<?php echo $display_email; ?>" class="input-locked" readonly title="University issued emails cannot be changed.">
                                    </div>
                                </div>
                                <div class="form-actions"><button type="submit" class="btn-main"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button></div>
                            </form>
                        </section>

                        <div class="nav-divider" style="margin: 36px 0;"></div>

                        <section class="settings-section">
                            <div class="card-head"><h2 class="card-title"><i class="fa-solid fa-shield-halved"></i> Security & Password</h2></div>
                            <form id="passwordForm" class="quick-form">
                                <div class="qf-field"><label class="form-label">Current Password</label><input type="password" name="current_password" required placeholder="Verify your current password"></div>
                                <div class="qf-row" style="margin-top: 12px;">
                                    <div><label class="form-label">New Password</label><input type="password" name="new_password" required placeholder="Must be at least 8 characters"></div>
                                    <div><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" required></div>
                                </div>
                                <div class="form-actions"><button type="submit" class="btn-main btn-dark"><i class="fa-solid fa-key"></i> Update Password</button></div>
                            </form>
                        </section>

                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="crop-modal-overlay" id="cropModal">
        <div class="crop-modal">
            <h2 style="font-family: 'Momo Trust Display', sans-serif; color: var(--navy); margin-bottom: 16px;">Adjust Profile Picture</h2>
            <div class="crop-container"><img id="imageToCrop" src=""></div>
            <div class="crop-actions">
                <button class="btn-cancel" id="cancelCrop">Cancel</button>
                <button class="btn-main" id="confirmCrop"><i class="fa-solid fa-cloud-arrow-up"></i> Save Picture</button>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            // Sidebar Toggle Logic for Mobile
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.add('open');
                });
            }

            setTimeout(() => document.querySelectorAll('.reveal-up').forEach(el => el.classList.add('active')), 100);

            function showToast(message, type = 'success') {
                const container = document.getElementById('toastContainer');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = type === 'success' ? `<i class="fa-solid fa-circle-check"></i> <span>${message}</span>` : `<i class="fa-solid fa-triangle-exclamation"></i> <span>${message}</span>`;
                container.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
                setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3500);
            }

            function handleAjaxForm(formId, endpoint) {
                const form = document.getElementById(formId);
                if (!form) return;
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = form.querySelector('button[type="submit"]');
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...'; btn.disabled = true;

                    fetch(endpoint, { method: 'POST', body: new FormData(this) })
                        .then(res => res.json())
                        .then(data => {
                            showToast(data.message || data.error, data.success ? 'success' : 'error');
                            
                            if (data.success) {
                                if (formId === 'passwordForm') form.reset(); 
                                
                                if (formId === 'profileForm' && data.new_name) {
                                    const userTitle = document.querySelector('.user-title');
                                    if (userTitle) userTitle.textContent = data.new_name;

                                    const sidebarName = document.querySelector('.sidebar-user .user-name');
                                    if (sidebarName) sidebarName.textContent = data.new_name;

                                    const initial = data.new_name.charAt(0).toUpperCase();
                                    const editorInitial = document.querySelector('.avatar-initial');
                                    if (editorInitial) editorInitial.textContent = initial;

                                    const topbarAvatar = document.getElementById('topbarAvatar');
                                    if (topbarAvatar && !topbarAvatar.querySelector('img')) {
                                        const fallbackDiv = topbarAvatar.querySelector('div');
                                        if (fallbackDiv) fallbackDiv.textContent = initial;
                                    }
                                }
                            }
                        })
                        .catch(() => showToast('Network error.', 'error'))
                        .finally(() => { btn.innerHTML = originalHtml; btn.disabled = false; });
                });
            }
            
            handleAjaxForm('profileForm', '/actions/update_profile.php');
            handleAjaxForm('passwordForm', '/actions/update_password.php');

            const avatarInput = document.getElementById('avatarInput');
            const cropModal = document.getElementById('cropModal');
            const imageToCrop = document.getElementById('imageToCrop');
            const cancelCrop = document.getElementById('cancelCrop');
            const confirmCrop = document.getElementById('confirmCrop');
            let cropper = null;

            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(event) {
                    imageToCrop.src = event.target.result;
                    cropModal.classList.add('active');
                    
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1, background: false, zoomable: true });
                };
                reader.readAsDataURL(file);
                e.target.value = ''; 
            });

            cancelCrop.addEventListener('click', () => { cropModal.classList.remove('active'); if (cropper) cropper.destroy(); });

            confirmCrop.addEventListener('click', () => {
                if (!cropper) return;
                const originalHtml = confirmCrop.innerHTML;
                confirmCrop.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
                confirmCrop.disabled = true;

                const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
                const base64Image = canvas.toDataURL('image/webp', 0.8);

                const formData = new FormData();
                formData.append('avatar_base64', base64Image);

                fetch('/actions/update_avatar.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');

                            const avatarPreview = document.getElementById('avatarPreview');
                            if (avatarPreview) avatarPreview.innerHTML = `<img src="${data.new_path}" style="width:100%;height:100%;object-fit:cover;"><div class="edit-overlay"><i class="fa-solid fa-camera"></i><span>Edit Picture</span></div>`;

                            const topbarAvatar = document.getElementById('topbarAvatar');
                            if (topbarAvatar) topbarAvatar.innerHTML = `<img src="${data.new_path}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;

                            const sidebarAvatar = document.getElementById('sidebarAvatar');
                            if (sidebarAvatar) sidebarAvatar.innerHTML = `<img src="${data.new_path}" style="width:100%;height:100%;object-fit:cover;">`;
                            
                            cropModal.classList.remove('active');
                            cropper.destroy();
                        } else { 
                            showToast(data.error, 'error'); 
                        }
                    })
                    .catch(() => showToast('Failed to upload image.', 'error'))
                    .finally(() => { confirmCrop.innerHTML = originalHtml; confirmCrop.disabled = false; });
            });
        });
    </script>
</body>
</html>