<?php
// Safely parse user profile info from session
$topbar_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
$topbar_pic = isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : '';

$name_parts = explode(',', $topbar_name);
$first_name_str = isset($name_parts[1]) ? trim($name_parts[1]) : trim($name_parts[0]);
$topbar_initial = $first_name_str !== '' ? strtoupper(substr($first_name_str, 0, 1)) : '?';

// Context Variables
$show_search = isset($show_topbar_search) ? $show_topbar_search : true; // Defaults to true for other pages
$search_mode = isset($topbar_search_mode) ? $topbar_search_mode : "local";
$search_placeholder = isset($topbar_search_placeholder) ? $topbar_search_placeholder : "Search...";
?>
<style>
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
        background: var(--bg-main, #fff);
        border-bottom: 1px solid var(--border-color, #e2e8f0);
        position: sticky;
        top: 0;
        z-index: 900;
    }

    <?php if ($show_search): ?>

    /* Search Bar CSS only loads if needed */
    .topbar-search {
        position: relative;
        flex: 1;
        max-width: 450px;
        display: flex;
        align-items: center;
    }

    .topbar-search i {
        position: absolute;
        left: 16px;
        color: var(--text-secondary, #64748b);
    }

    .topbar-search input {
        width: 100%;
        padding: 10px 16px 10px 42px;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 20px;
        background: #f8fafc;
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
        transition: 0.2s;
    }

    .topbar-search input:focus {
        background: #fff;
        border-color: var(--navy-bright, #0d6efd);
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    <?php endif; ?>.topbar-right {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-left: auto;
    }

    .topbar-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--border-color, #e2e8f0);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        display: block;
        text-decoration: none;
        transition: transform 0.2s;
    }

    .topbar-avatar:hover {
        transform: scale(1.05);
        border-color: var(--navy-bright, #0d6efd);
    }
</style>

<header class="topbar">
    <button class="menu-toggle" id="menuToggle" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-secondary); margin-right: 15px; display:none;">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php if ($show_search): ?>
        <div class="topbar-search" id="tbSearchWrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="tbSearchInput"
                placeholder="<?php echo htmlspecialchars($search_placeholder); ?>"
                data-mode="<?php echo htmlspecialchars($search_mode); ?>"
                autocomplete="off">
        </div>
    <?php endif; ?>

    <div class="topbar-right">
        <a href="/views/profile.php" class="topbar-avatar">
            <?php if (!empty($topbar_pic)): ?>
                <img src="<?php echo htmlspecialchars($topbar_pic); ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display='none'">
            <?php else: ?>
                <div style="width:100%; height:100%; background:var(--navy-bright, #0d6efd); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.1rem;">
                    <?php echo $topbar_initial; ?>
                </div>
            <?php endif; ?>
        </a>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // Mobile Sidebar Toggle (Always Needed)
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        if (menuToggle && sidebar) {
            if (window.innerWidth <= 1024) menuToggle.style.display = 'block';
            menuToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
            window.addEventListener('resize', () => {
                menuToggle.style.display = window.innerWidth <= 1024 ? 'block' : 'none';
            });
        }

        <?php if ($show_search): ?>
            // --- SEARCH LOGIC ONLY INJECTED IF SEARCH BAR EXISTS ---
            const searchInput = document.getElementById('tbSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const q = e.target.value.toLowerCase().trim();
                    // Broadcast the search query to the current page (Tasks, Resources, etc.)
                    document.dispatchEvent(new CustomEvent('pageSearch', {
                        detail: q
                    }));
                });
            }
        <?php endif; ?>

    });
</script>