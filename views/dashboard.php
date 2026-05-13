<?php
date_default_timezone_set('Asia/Manila');

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';

if (strpos($full_name, ',') !== false) {
    $parts = explode(',', $full_name);
    $target = isset($parts[1]) ? $parts[1] : $parts[0];
    $first_name = explode(' ', trim($target))[0];
} else {
    $first_name = explode(' ', trim($full_name))[0];
}

$hour = date('H');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
$show_topbar_search = false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TalaAral</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <style>
        h1 {
            font-family: 'Momo Trust Display', sans-serif;
            font-weight: normal;
        }

        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 9999;
        }

        .toast {
            background: var(--bg-main, #fff);
            color: var(--navy, #0f172a);
            padding: 14px 20px;
            border-radius: var(--radius-sm, 8px);
            box-shadow: var(--shadow-md, 0 4px 12px rgba(0, 0, 0, 0.1));
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-left: 4px solid var(--navy-bright, #0052FF);
        }

        .toast.toast-success {
            border-left-color: var(--green, #10b981);
        }

        .toast.toast-success i {
            color: var(--green, #10b981);
        }

        .toast.toast-error {
            border-left-color: var(--red, #ef4444);
        }

        .toast.toast-error i {
            color: var(--red, #ef4444);
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .stat-card.has-overdue {
            border: 1px solid rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.02);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.05);
        }

        .stat-card.has-overdue .stat-num {
            color: #ef4444;
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.2);
        }
    </style>
</head>

<body>
    <div class="flare-bg">
        <div class="flare orb-navy"></div>
        <div class="flare orb-gold"></div>
    </div>

    <div class="app-shell">
        <?php include 'components/sidebar.php'; ?>
        <div class="main-wrap">
            <?php include 'components/topbar.php'; ?>

            <main class="content">
                <div class="welcome-banner reveal-up">
                    <div class="welcome-text">
                        <p class="welcome-date"><?php echo date('l, F j, Y'); ?></p>
                        <h1><?php echo $greeting; ?>, <span class="text-gradient"><?php echo htmlspecialchars($first_name); ?></span>.</h1>
                        <p>Here's what's happening in your academic workspace today.</p>
                    </div>
                    <div class="welcome-orbs">
                        <div class="w-orb w-orb-1"></div>
                        <div class="w-orb w-orb-2"></div>
                    </div>
                </div>

                <div class="stats-row reveal-up" style="transition-delay: 0.1s;">
                    <div class="stat-card">
                        <div class="stat-icon-wrap blue"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div class="stat-body">
                            <div class="stat-num" id="stat-pending"><i class="fa-solid fa-spinner fa-spin fa-xs"></i></div>
                            <div class="stat-label">Pending Tasks</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrap" style="background: rgba(0, 102, 255, 0.1); color: #0066ff;"><i class="fa-solid fa-spinner"></i></div>
                        <div class="stat-body">
                            <div class="stat-num" id="stat-progress"><i class="fa-solid fa-spinner fa-spin fa-xs"></i></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrap green"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-body">
                            <div class="stat-num" id="stat-completed"><i class="fa-solid fa-spinner fa-spin fa-xs"></i></div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                    <div class="stat-card" id="card-overdue">
                        <div class="stat-icon-wrap red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="stat-body">
                            <div class="stat-num" id="stat-overdue"><i class="fa-solid fa-spinner fa-spin fa-xs"></i></div>
                            <div class="stat-label">Overdue</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrap gold"><i class="fa-solid fa-calendar-check"></i></div>
                        <div class="stat-body">
                            <div class="stat-num" id="stat-today"><i class="fa-solid fa-spinner fa-spin fa-xs"></i></div>
                            <div class="stat-label">Due Today</div>
                        </div>
                    </div>
                </div>

                <div class="dash-grid reveal-up" style="transition-delay: 0.2s;">
                    <div class="glass-card">
                        <div class="card-head">
                            <h2 class="card-title"><i class="fa-solid fa-clock"></i> Upcoming Deadlines</h2>
                            <a href="/tasks" class="card-link">View all <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div id="deadline-list">
                            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Loading deadlines...</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-head">
                            <h2 class="card-title"><i class="fa-solid fa-book-open"></i> Latest Resources</h2>
                            <a href="/resources" class="card-link">View all <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div id="resources-list">
                            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Loading resources...</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-head">
                            <h2 class="card-title"><i class="fa-solid fa-newspaper"></i> RTU Updates</h2>
                            <a href="/updates" class="card-link">View all <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div id="updates-list">
                            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Loading updates...</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-head">
                            <h2 class="card-title"><i class="fa-solid fa-plus"></i> Quick Add Task</h2>
                        </div>
                        <form class="quick-form" id="quickAddForm" action="/actions/task_action.php" method="POST">
                            <input type="hidden" name="action" value="create">
                            <div class="qf-field"><input type="text" name="title" placeholder="Task title..." required></div>
                            <div class="qf-row">
                                <input type="text" name="course" placeholder="Course / Subject...">
                                <input type="datetime-local" name="due_date">
                            </div>
                            <div class="qf-row">
                                <select name="priority" class="premium-select">
                                    <option value="low">Low Priority</option>
                                    <option value="medium" selected>Medium Priority</option>
                                    <option value="high">High Priority</option>
                                </select>
                                <button type="submit" class="btn-main" id="quickAddBtn"><i class="fa-solid fa-plus"></i> Add Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const escapeHTML = str => {
                const d = document.createElement('div');
                d.textContent = str ?? '';
                return d.innerHTML;
            };

            setTimeout(() => {
                document.querySelectorAll('.reveal-up').forEach(el => el.classList.add('active'));
            }, 100);

            // Fetch exactly what is needed for the dashboard cards, nothing more.
            function loadDashboardData() {
                fetch('/backend/api/dashboard_data.php')
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('stat-pending').innerText = data.stats.pending || 0;
                        document.getElementById('stat-progress').innerText = data.stats.in_progress || 0;
                        document.getElementById('stat-completed').innerText = data.stats.completed || 0;
                        document.getElementById('stat-today').innerText = data.stats.today || 0;

                        const overdueEl = document.getElementById('stat-overdue');
                        const overdueCard = document.getElementById('card-overdue');
                        const overdueCount = data.stats.overdue || 0;
                        overdueEl.innerText = overdueCount;
                        if (overdueCount > 0) overdueCard.classList.add('has-overdue');
                        else overdueCard.classList.remove('has-overdue');

                        const deadlineList = document.getElementById('deadline-list');
                        if (data.upcoming && data.upcoming.length > 0) {
                            deadlineList.innerHTML = data.upcoming.map(task => {
                                const due = new Date(task.due_date);
                                const fDate = due.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit'
                                });
                                return `
                                <div class="deadline-item">
                                    <div class="priority-dot priority-${task.priority}"></div>
                                    <div class="deadline-info">
                                        <div class="deadline-title">${escapeHTML(task.title)}</div>
                                        <div class="deadline-subject">${escapeHTML(task.course || 'General')}</div>
                                    </div>
                                    <div class="deadline-date"><i class="fa-solid fa-calendar"></i> ${fDate}</div>
                                </div>`;
                            }).join('');
                        } else {
                            deadlineList.innerHTML = `<div class="empty-state"><i class="fa-solid fa-inbox"></i><p>No upcoming deadlines</p></div>`;
                        }
                    })
                    .catch(err => console.error('Dashboard data error:', err));
            }

            function loadResourcesPreview() {
                fetch('/backend/api/get_canvas_data.php?preview=1').then(r => r.json()).then(data => {
                    const list = document.getElementById('resources-list');
                    if (data.error || !Array.isArray(data) || data.length === 0) {
                        list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-folder-open"></i><p>No new resources</p></div>`;
                        return;
                    }
                    list.innerHTML = data.map(f => `
                        <a class="resource-item" href="/resources">
                            <div class="resource-icon resource-icon--${escapeHTML(f.type)}"><i class="fa-solid fa-file"></i></div>
                            <div class="resource-info">
                                <div class="resource-title">${escapeHTML(f.title)}</div>
                                <div class="resource-meta"><span class="resource-course">${escapeHTML(f.course_name)}</span></div>
                            </div>
                        </a>`).join('');
                });
            }

            function loadLatestUpdates() {
                fetch('/backend/api/get_rtu_updates.php?preview=1').then(r => r.json()).then(data => {
                    const list = document.getElementById('updates-list');
                    if (data.error || !Array.isArray(data) || data.length === 0) {
                        list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-bullhorn"></i><p>No new updates</p></div>`;
                        return;
                    }
                    list.innerHTML = data.map(u => `
                        <a class="update-item" href="/updates?open=${encodeURIComponent(u.url)}">
                            <div class="update-title">${escapeHTML(u.title)}</div>
                            <div class="update-date"><i class="fa-solid fa-calendar-days"></i> ${escapeHTML(u.date)}</div>
                        </a>`).join('');
                });
            }

            const quickAddForm = document.getElementById('quickAddForm');
            if (quickAddForm) {
                quickAddForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = document.getElementById('quickAddBtn');
                    const originalHTML = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

                    fetch('/actions/task_action.php', {
                            method: 'POST',
                            body: new FormData(this)
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Task added successfully!', 'success');
                                quickAddForm.reset();
                                loadDashboardData();
                            }
                        }).finally(() => {
                            submitBtn.innerHTML = originalHTML;
                        });
                });
            }

            function showToast(message, type = 'success') {
                const container = document.getElementById('toastContainer');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = (type === 'success' ? '<i class="fa-solid fa-circle-check"></i>' : '<i class="fa-solid fa-triangle-exclamation"></i>') + `<span>${message}</span>`;
                container.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 400);
                }, 3500);
            }

            loadDashboardData();
            loadResourcesPreview();
            loadLatestUpdates();
        });
    </script>
</body>

</html>