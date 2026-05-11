<?php
// Get the current file name so we can highlight the active menu item
$current_page = basename($_SERVER['PHP_SELF']);

// Display name fallback chain
$display_name    = isset($full_name) 
    ? $full_name 
    : (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Registered User');

// Program abbreviation fallback
$display_program = isset($_SESSION['program_abbreviation']) && !empty($_SESSION['program_abbreviation'])
    ? $_SESSION['program_abbreviation']
    : 'BSIT'; // Contextual fallback

// Year level fallback (Checks if parent page defined it, otherwise checks session)
$display_year = isset($year_level) && !empty($year_level)
    ? $year_level
    : (isset($_SESSION['year_level']) ? $_SESSION['year_level'] : '');

// Construct the final string: "BSIT - 2nd Year" 
$sidebar_role = !empty($display_year) 
    ? trim($display_program . ' - ' . $display_year) 
    : trim($display_program . ' Scholar');

// --- NEW: Global Profile Picture Logic ---
$sidebar_pic = $_SESSION['profile_pic'] ?? '';
$sidebar_has_avatar = !empty($sidebar_pic) && strpos($sidebar_pic, 'data:image') === 0;
$sidebar_initial = !empty($display_name) ? strtoupper(substr(trim($display_name), 0, 1)) : '?';
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-headings">
            <div class="logo">Tala<span>Aral</span></div>
            <div class="badge">Academic Workspace</div>
        </div>
        <button class="mobile-close-btn" onclick="document.getElementById('sidebar').classList.remove('open')" aria-label="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="avatar-ring" id="sidebarAvatar" style="overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--navy-bright, #0d6efd); color: #fff; font-family: var(--font-title, sans-serif); font-size: 1.2rem;">
            <?php if ($sidebar_has_avatar): ?>
                <img src="<?php echo $sidebar_pic; ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <?php echo $sidebar_initial; ?>
            <?php endif; ?>
        </div>
        
        <div class="user-meta">
            <span class="user-name"><?php echo htmlspecialchars($display_name); ?></span>
            <span class="user-role">
                <i class="fa-solid fa-circle status-dot"></i>
                <?php echo htmlspecialchars($sidebar_role); ?>
            </span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-section-label">Main</p>
        <a href="/views/dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i><span>Dashboard</span>
        </a>
        <a href="/views/tasks.php" class="nav-item <?php echo $current_page == 'tasks.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-list-check"></i><span>Tasks</span>
            <span class="nav-badge" id="task-badge"></span>
        </a>
        <a href="/views/calendar.php" class="nav-item <?php echo $current_page == 'calendar.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-days"></i><span>Calendar</span>
        </a>

        <p class="nav-section-label">Content</p>
        <a href="/views/resources.php" class="nav-item <?php echo $current_page == 'resources.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-open"></i><span>Resources</span>
        </a>
        <a href="/views/updates.php" class="nav-item <?php echo $current_page == 'updates.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-newspaper"></i><span>Updates</span>
        </a>

        <p class="nav-section-label">Account</p>
        <a href="/views/profile.php" class="nav-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-circle-user"></i><span>Profile</span>
        </a>
        <div class="nav-divider"></div>
        <a href="/actions/logout_action.php" class="nav-item nav-logout">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
        </a>
    </nav>
</aside>