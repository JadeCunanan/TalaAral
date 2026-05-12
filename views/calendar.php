<?php
date_default_timezone_set('Asia/Manila');
session_start();

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

require_once '../backend/includes/db.php';

$user_id = $_SESSION['user_id'];

// --- Configure the Modular Topbar for this specific page ---
$topbar_search_placeholder = "Search specific deadlines...";
$topbar_search_mode = "local";

/**
 * 1. FETCH DYNAMIC DATA
 * We pull strictly from the tasks table. Announcements have been removed.
 */

// Fetch User Tasks
$stmtTasks = $pdo->prepare("
    SELECT task_id, title, course, due_date, priority, status 
    FROM tasks 
    WHERE user_id = ? AND due_date IS NOT NULL AND status != 'archived'
    ORDER BY due_date ASC
");
$stmtTasks->execute([$user_id]);
$tasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

// Encode for JavaScript injection
$tasks_json = json_encode($tasks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | TalaAral</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/calendar.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
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
                <div class="page-header reveal-up">
                    <h1 style="font-family: 'Momo Trust Display', sans-serif;">Academic <span class="text-gradient">Calendar</span></h1>
                    <p>Track your coursework deadlines and schedule your study sessions.</p>
                </div>

                <div class="calendar-wrapper reveal-up" style="transition-delay: 0.1s;">
                    <div class="cal-header">
                        <h2 class="cal-title" id="monthYearDisplay">Loading...</h2>
                        <div class="cal-nav">
                            <button onclick="changeMonth(-1)" title="Previous Month"><i class="fa-solid fa-chevron-left"></i></button>
                            <button onclick="changeMonth(1)" title="Next Month"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <div class="cal-grid" id="calendarGrid">
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal-overlay" id="dayModal">
        <div class="modal-card">
            <button class="close-btn" onclick="closeDayModal()"><i class="fa-solid fa-xmark"></i></button>

            <div class="modal-header">
                <h3 id="dayModalTitle"><i class="fa-solid fa-calendar-day"></i> Date Details</h3>
            </div>

            <div class="modal-body day-details" id="dayModalBody">
            </div>

            <div class="modal-actions calendar-modal-actions">
                <button type="button" class="btn-main" onclick="window.location.href='/tasks'">
                    Manage Tasks <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        const rawTasks = <?php echo $tasks_json; ?>;
        let currentDate = new Date();

        document.addEventListener('DOMContentLoaded', () => {
            // Animation Trigger
            setTimeout(() => {
                document.querySelectorAll('.reveal-up').forEach(el => el.classList.add('active'));
            }, 100);

            renderCalendar();

            // ── TOPBAR SEARCH LISTENER ──
            document.addEventListener('pageSearch', (e) => {
                const term = e.detail.toLowerCase();
                const cells = document.querySelectorAll('.cal-cell:not(.empty)');

                cells.forEach(cell => {
                    const content = cell.innerText.toLowerCase();
                    if (content.includes(term)) {
                        cell.style.opacity = "1";
                        cell.style.transform = "scale(1)";
                        cell.style.filter = "none";
                    } else {
                        cell.style.opacity = "0.2";
                        cell.style.transform = "scale(0.95)";
                        cell.style.filter = "grayscale(100%)";
                    }
                });
            });

            // Close modal on overlay click
            document.getElementById('dayModal').addEventListener('click', (e) => {
                if (e.target.id === 'dayModal') closeDayModal();
            });
        });

        function renderCalendar() {
            const month = currentDate.getMonth();
            const year = currentDate.getFullYear();
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            document.getElementById('monthYearDisplay').innerText = `${monthNames[month]} ${year}`;

            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';

            // Add Day Headers
            ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(d => {
                grid.innerHTML += `<div class="cal-day-name">${d}</div>`;
            });

            const firstDayIndex = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();

            // Leading empty cells
            for (let i = 0; i < firstDayIndex; i++) {
                grid.innerHTML += `<div class="cal-cell empty"></div>`;
            }

            const today = new Date();
            const isThisMonth = today.getMonth() === month && today.getFullYear() === year;

            for (let i = 1; i <= totalDays; i++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                const isToday = isThisMonth && i === today.getDate();

                // Filter Tasks for this cell
                const dayTasks = rawTasks.filter(t => t.due_date.startsWith(dateStr));

                let eventsHtml = '';
                let visibleCount = 0;
                const LIMIT = 2;

                dayTasks.forEach(t => {
                    if (visibleCount < LIMIT) {
                        const statusClass = t.status === 'completed' ? 'evt-completed' : `evt-${t.priority}`;
                        const strike = t.status === 'completed' ? 'style="text-decoration:line-through; opacity:0.6;"' : '';
                        eventsHtml += `<div class="cal-evt ${statusClass}"><span ${strike}>${t.title}</span></div>`;
                        visibleCount++;
                    }
                });

                const total = dayTasks.length;
                if (total > LIMIT) {
                    eventsHtml += `<div class="cal-more-link">+${total - LIMIT} more</div>`;
                }

                grid.innerHTML += `
                    <div class="cal-cell ${isToday ? 'today' : ''}" onclick="openDayModal('${dateStr}')">
                        <div class="cal-date">
                            <span>${i}</span>
                            ${isToday ? '<span class="today-badge">Today</span>' : ''}
                        </div>
                        <div class="cal-events-wrap">${eventsHtml}</div>
                    </div>`;
            }
        }

        function changeMonth(dir) {
            currentDate.setMonth(currentDate.getMonth() + dir);
            renderCalendar();
        }

        function openDayModal(dateStr) {
            // Fix 12AM vs 8AM: Force local parsing
            const dateObj = new Date(dateStr + "T00:00:00");
            const titleDate = dateObj.toLocaleDateString('en-US', {
                dateStyle: 'full'
            });

            document.getElementById('dayModalTitle').innerHTML = `<i class="fa-solid fa-calendar-day"></i> ${titleDate}`;

            const tasks = rawTasks.filter(t => t.due_date.startsWith(dateStr));
            const body = document.getElementById('dayModalBody');
            body.innerHTML = '';

            if (tasks.length === 0) {
                body.innerHTML = `<div class="empty-state" style="padding: 40px 0;"><i class="fa-solid fa-mug-hot"></i><p>No deadlines for this day.</p></div>`;
            } else {
                tasks.forEach(t => {
                    // Safe Time formatting
                    const taskTime = new Date(t.due_date.replace(' ', 'T')).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit'
                    });
                    const isDone = t.status === 'completed';

                    body.innerHTML += `
                        <div class="detail-card ${isDone ? 'completed' : t.priority}">
                            <div class="dc-info">
                                <h4 class="dc-title" style="${isDone ? 'text-decoration:line-through; color:var(--text-secondary);' : ''}">${t.title}</h4>
                                <span class="dc-meta"><i class="fa-solid fa-book"></i> ${t.course || 'General Task'}</span>
                            </div>
                            <div class="dc-status">
                                <span class="dc-time">${isDone ? '' : taskTime}</span>
                                <span class="dc-badge badge-${isDone ? 'completed' : 'pending'}">${isDone ? 'Done' : 'Pending'}</span>
                            </div>
                        </div>`;
                });
            }

            document.getElementById('dayModal').classList.add('active');
        }

        function closeDayModal() {
            document.getElementById('dayModal').classList.remove('active');
        }
    </script>
</body>

</html>