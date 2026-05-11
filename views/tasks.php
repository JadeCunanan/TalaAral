<?php
date_default_timezone_set('Asia/Manila');

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

require_once '../backend/includes/db.php';

$user_id = $_SESSION['user_id'];

// ── Configure the Modular Topbar ──
$topbar_search_placeholder = "Search tasks or courses...";
$topbar_search_mode = "local"; 

// ==========================================
// OVERDUE AUTOMATION ENGINE (Manila Time Synced)
// ==========================================
$phpNow = date('Y-m-d H:i:s'); 

$pdo->prepare("
    UPDATE tasks 
    SET previous_status = status, status = 'missed' 
    WHERE user_id = ?
    AND status IN ('pending', 'in_progress')
    AND due_date IS NOT NULL 
    AND due_date < ?
")->execute([$user_id, $phpNow]);

// Fetch all tasks including the reminder columns
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC, created_at DESC");
$stmt->execute([$user_id]);
$all_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tasks_pending    = [];
$tasks_progress   = [];
$tasks_completed  = [];
$tasks_archived   = [];

foreach ($all_tasks as $task) {
    if ($task['status'] === 'pending' || $task['status'] === 'missed') {
        $tasks_pending[] = $task;
    } elseif ($task['status'] === 'in_progress') {
        $tasks_progress[] = $task;
    } elseif ($task['status'] === 'completed') {
        $tasks_completed[] = $task;
    } elseif ($task['status'] === 'archived') {
        $tasks_archived[] = $task;
    }
}

$status_labels = [
    'pending'     => 'Pending',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
    'missed'      => 'Pending',
];

function formatTaskDate(?string $datetime_string, string $status): string
{
    if (!$datetime_string) {
        return '<div class="tc-date"><i class="fa-solid fa-calendar-minus"></i> No Deadline</div>';
    }

    $due  = new DateTime($datetime_string);
    $now  = new DateTime(); 
    $dueDay   = (clone $due)->setTime(0, 0, 0);
    $today    = (clone $now)->setTime(0, 0, 0);

    if ($status === 'missed' || $due < $now) {
        return '<div class="tc-date overdue"><i class="fa-solid fa-circle-exclamation"></i> Overdue — ' . $due->format('M j, g:i A') . '</div>';
    } elseif ($dueDay == $today) {
        return '<div class="tc-date today"><i class="fa-solid fa-fire"></i> Due Today (' . $due->format('g:i A') . ')</div>';
    } else {
        return '<div class="tc-date"><i class="fa-solid fa-calendar"></i> ' . $due->format('M j — g:i A') . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks | TalaAral</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/tasks.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <style>
        h1 {
            font-family: 'Momo Trust Display', sans-serif;
            font-weight: normal;
        }
        /* Style for the Task Search Empty State */
        .search-empty {
            display: none;
            padding: 40px 20px;
            text-align: center;
            color: var(--text-secondary);
        }
        .search-empty i { font-size: 2rem; margin-bottom: 12px; opacity: 0.5; }
    </style>
</head>

<body>

    <div class="flare-bg">
        <div class="flare orb-navy"></div>
        <div class="flare orb-gold"></div>
    </div>

    <div class="app-shell grid-container">

        <?php include 'components/sidebar.php'; ?>

        <div class="main-wrap">

            <?php include 'components/topbar.php'; ?>

            <main class="content">

                <div class="page-header reveal-up" style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h1>Academic <span class="text-gradient">Tasks</span></h1>
                        <p>Manage your coursework, projects, and student responsibilities</p>
                    </div>
                    <button class="btn-main new-task-btn" onclick="openTaskModal()" style="margin-bottom: 8px;">
                        <i class="fa-solid fa-plus"></i> New Task
                    </button>
                </div>

                <div class="kanban-board reveal-up" style="transition-delay: 0.1s;">

                    <div class="kanban-col">
                        <div class="k-head">
                            <div class="k-title"><i class="fa-solid fa-circle-dot" style="color: var(--text-secondary);"></i> Pending</div>
                            <span class="k-count"><?php echo count($tasks_pending); ?></span>
                        </div>
                        <div class="k-body" id="col-pending">
                            <?php if (empty($tasks_pending)): ?>
                                <div class="empty-state default-empty"><i class="fa-solid fa-inbox"></i><p>No pending tasks.</p></div>
                            <?php endif; ?>
                            <?php foreach ($tasks_pending as $task): 
                                $isOverdue = $task['status'] === 'missed';
                            ?>
                                <div class="task-card <?php echo $isOverdue ? 'overdue-card' : ''; ?>"
                                    data-id="<?php echo $task['task_id']; ?>"
                                    data-title="<?php echo htmlspecialchars($task['title']); ?>"
                                    data-course="<?php echo htmlspecialchars($task['course'] ?? ''); ?>"
                                    data-due="<?php echo $task['due_date'] ? date('Y-m-d\TH:i', strtotime($task['due_date'])) : ''; ?>"
                                    data-priority="<?php echo $task['priority']; ?>"
                                    data-status="<?php echo $task['status']; ?>"
                                    data-reminder-value="<?php echo $task['reminder_value']; ?>"
                                    data-reminder-unit="<?php echo $task['reminder_unit']; ?>"
                                    onclick="openEditModal(this)">
                                    <div class="tc-top">
                                        <span class="tc-subject"><?php echo htmlspecialchars($task['course'] ?: 'General'); ?></span>
                                        <div class="priority-dot priority-<?php echo $task['priority']; ?>"></div>
                                    </div>
                                    <h3 class="tc-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                    <div class="tc-bot"><?php echo formatTaskDate($task['due_date'], $task['status']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="kanban-col">
                        <div class="k-head">
                            <div class="k-title"><i class="fa-solid fa-spinner" style="color: var(--navy-bright);"></i> In Progress</div>
                            <span class="k-count"><?php echo count($tasks_progress); ?></span>
                        </div>
                        <div class="k-body" id="col-progress">
                            <?php if (empty($tasks_progress)): ?>
                                <div class="empty-state default-empty"><i class="fa-solid fa-person-running"></i><p>Nothing in progress.</p></div>
                            <?php endif; ?>
                            <?php foreach ($tasks_progress as $task): ?>
                                <div class="task-card"
                                    data-id="<?php echo $task['task_id']; ?>"
                                    data-title="<?php echo htmlspecialchars($task['title']); ?>"
                                    data-course="<?php echo htmlspecialchars($task['course'] ?? ''); ?>"
                                    data-due="<?php echo $task['due_date'] ? date('Y-m-d\TH:i', strtotime($task['due_date'])) : ''; ?>"
                                    data-priority="<?php echo $task['priority']; ?>"
                                    data-status="<?php echo $task['status']; ?>"
                                    data-reminder-value="<?php echo $task['reminder_value']; ?>"
                                    data-reminder-unit="<?php echo $task['reminder_unit']; ?>"
                                    onclick="openEditModal(this)">
                                    <div class="tc-top">
                                        <span class="tc-subject"><?php echo htmlspecialchars($task['course'] ?: 'General'); ?></span>
                                        <div class="priority-dot priority-<?php echo $task['priority']; ?>"></div>
                                    </div>
                                    <h3 class="tc-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                    <div class="tc-bot"><?php echo formatTaskDate($task['due_date'], $task['status']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="kanban-col">
                        <div class="k-head">
                            <div class="k-title"><i class="fa-solid fa-circle-check" style="color: var(--green);"></i> Completed</div>
                            <span class="k-count"><?php echo count($tasks_completed); ?></span>
                        </div>
                        <div class="k-body" id="col-completed">
                            <?php if (empty($tasks_completed)): ?>
                                <div class="empty-state default-empty"><i class="fa-solid fa-check-double"></i><p>No completed tasks.</p></div>
                            <?php endif; ?>
                            <?php foreach ($tasks_completed as $task): ?>
                                <div class="task-card completed-card" style="opacity: 0.7;"
                                    data-id="<?php echo $task['task_id']; ?>"
                                    data-title="<?php echo htmlspecialchars($task['title']); ?>"
                                    data-course="<?php echo htmlspecialchars($task['course'] ?? ''); ?>"
                                    data-due="<?php echo $task['due_date'] ? date('Y-m-d\TH:i', strtotime($task['due_date'])) : ''; ?>"
                                    data-priority="<?php echo $task['priority']; ?>"
                                    data-status="<?php echo $task['status']; ?>"
                                    data-reminder-value="<?php echo $task['reminder_value']; ?>"
                                    data-reminder-unit="<?php echo $task['reminder_unit']; ?>"
                                    onclick="openEditModal(this)">
                                    <div class="tc-top">
                                        <span class="tc-subject"><?php echo htmlspecialchars($task['course'] ?: 'General'); ?></span>
                                        <div class="priority-dot priority-<?php echo $task['priority']; ?>"></div>
                                    </div>
                                    <h3 class="tc-title" style="text-decoration: line-through;"><?php echo htmlspecialchars($task['title']); ?></h3>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="archive-container reveal-up" style="transition-delay: 0.2s;">
                    <button class="btn-ghost archive-toggle-btn" onclick="toggleArchive()">
                        <i class="fa-solid fa-box-archive"></i> View Archived Tasks (<?php echo count($tasks_archived); ?>)
                    </button>
                    <div class="archive-section" id="archiveSection">
                        <?php if (empty($tasks_archived)): ?>
                            <div class="empty-state"><i class="fa-solid fa-wind"></i><p>Your archive is empty.</p></div>
                        <?php else: ?>
                            <div class="archive-list">
                                <?php foreach ($tasks_archived as $task):
                                    $prev_key   = $task['previous_status'] ?? 'pending';
                                    $prev_label = $status_labels[$prev_key] ?? 'Pending';
                                ?>
                                    <div class="archive-item">
                                        <div class="ai-left">
                                            <div class="priority-dot priority-<?php echo $task['priority']; ?>"></div>
                                            <div>
                                                <h4 class="ai-title"><?php echo htmlspecialchars($task['title']); ?></h4>
                                                <span class="ai-course"><?php echo htmlspecialchars($task['course'] ?: 'General'); ?></span>
                                            </div>
                                        </div>
                                        <div class="ai-right">
                                            <button class="btn-restore" onclick="unarchiveTask(<?php echo $task['task_id']; ?>, '<?php echo $prev_label; ?>')">
                                                <i class="fa-solid fa-arrow-rotate-left"></i> Restore to <?php echo $prev_label; ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="priority-legend reveal-up" style="transition-delay: 0.3s;">
                    <span class="legend-title"><i class="fa-solid fa-circle-info"></i> Priority</span>
                    <div class="legend-items">
                        <div class="l-item"><span class="priority-dot priority-high"></span> High</div>
                        <div class="l-item"><span class="priority-dot priority-medium"></span> Medium</div>
                        <div class="l-item"><span class="priority-dot priority-low"></span> Low</div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <div class="modal-overlay" id="newTaskModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-layer-group"></i> Create New Task</h3>
                <button class="close-btn" onclick="closeTaskModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="quick-form" id="createTaskForm" action="/actions/task_action.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="qf-field">
                    <label>Task Title</label>
                    <input type="text" name="title" placeholder="e.g., Chapter 4 Reading..." required>
                </div>
                <div class="qf-field">
                    <label>Course</label>
                    <input type="text" name="course" placeholder="e.g., Integrative Programming">
                </div>
                <div class="qf-row">
                    <div class="qf-field">
                        <label>Deadline Option</label>
                        <select name="deadline_type" id="deadlineType" class="premium-select" onchange="toggleDateInput('deadlineType','dateInputContainer','dueDateInput')">
                            <option value="none" selected>No Deadline</option>
                            <option value="set">Set a Deadline</option>
                        </select>
                    </div>
                    <div class="qf-field date-reveal" id="dateInputContainer">
                        <label>Date &amp; Time</label>
                        <input type="datetime-local" name="due_date" id="dueDateInput">
                    </div>
                </div>
                <div class="qf-field">
                    <label><i class="fa-solid fa-bell"></i> Set Email Reminder</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="reminder_value" id="createReminderValue" placeholder="0" min="0" class="premium-input" style="flex: 1;">
                        <select name="reminder_unit" id="createReminderUnit" class="premium-select" style="flex: 2;">
                            <option value="none" selected>No Reminder</option>
                            <option value="minutes">Minutes before</option>
                            <option value="hours">Hours before</option>
                            <option value="days">Days before</option>
                            <option value="weeks">Weeks before</option>
                        </select>
                    </div>
                </div>
                <div class="qf-field">
                    <label>Priority Level</label>
                    <div class="priority-options">
                        <label class="priority-radio low"><input type="radio" name="priority" value="low"><span class="p-pill">Low</span></label>
                        <label class="priority-radio medium"><input type="radio" name="priority" value="medium" checked><span class="p-pill">Medium</span></label>
                        <label class="priority-radio high"><input type="radio" name="priority" value="high"><span class="p-pill">High</span></label>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-ghost" onclick="closeTaskModal()">Cancel</button>
                    <button type="submit" class="btn-main">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editTaskModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-pen-to-square"></i> Task Details</h3>
                <button class="close-btn" onclick="closeEditModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="quick-form" id="editTaskForm" action="/actions/task_action.php" method="POST">
                <input type="hidden" name="action" id="editTaskAction" value="update">
                <input type="hidden" name="task_id" id="editTaskId">
                <div class="qf-field"><label>Task Title</label><input type="text" name="title" id="editTaskTitle" required></div>
                <div class="qf-field"><label>Course</label><input type="text" name="course" id="editTaskCourse"></div>
                <div class="qf-row">
                    <div class="qf-field">
                        <label>Deadline Option</label>
                        <select name="deadline_type" id="editDeadlineType" class="premium-select" onchange="toggleDateInput('editDeadlineType','editDateInputContainer','editDueDateInput')">
                            <option value="none">No Deadline</option>
                            <option value="set">Set a Deadline</option>
                        </select>
                    </div>
                    <div class="qf-field date-reveal" id="editDateInputContainer">
                        <label>Date &amp; Time</label>
                        <input type="datetime-local" name="due_date" id="editDueDateInput">
                    </div>
                </div>
                <div class="qf-field">
                    <label>Task Status</label>
                    <select name="status" id="editTaskStatus" class="premium-select">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="qf-field">
                    <label><i class="fa-solid fa-bell"></i> Set Email Reminder</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="reminder_value" id="editReminderValue" min="0" class="premium-input" style="flex: 1;">
                        <select name="reminder_unit" id="editReminderUnit" class="premium-select" style="flex: 2;">
                            <option value="none">No Reminder</option>
                            <option value="minutes">Minutes before</option>
                            <option value="hours">Hours before</option>
                            <option value="days">Days before</option>
                            <option value="weeks">Weeks before</option>
                        </select>
                    </div>
                </div>
                <div class="qf-field">
                    <label>Priority Level</label>
                    <div class="priority-options" id="editPriorityOptions">
                        <label class="priority-radio low"><input type="radio" name="priority" value="low"><span class="p-pill">Low</span></label>
                        <label class="priority-radio medium"><input type="radio" name="priority" value="medium"><span class="p-pill">Medium</span></label>
                        <label class="priority-radio high"><input type="radio" name="priority" value="high"><span class="p-pill">High</span></label>
                    </div>
                </div>
                <div class="modal-actions edit-actions">
                    <button type="button" class="btn-ghost-danger" onclick="archiveTask()">
                        <i class="fa-solid fa-box-archive"></i> Archive
                    </button>
                    <div class="action-group">
                        <button type="button" class="btn-ghost" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn-main">Update Task</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="confirmModal">
        <div class="modal-card confirm-card">
            <div class="modal-header">
                <h3 id="confirmTitle"><i class="fa-solid fa-circle-exclamation"></i> Confirm</h3>
                <button class="close-btn" onclick="closeConfirmModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body"><p id="confirmMessage">Are you sure you want to proceed?</p></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn-main btn-danger-fill" id="confirmActionBtn">Proceed</button>
            </div>
        </div>
    </div>

    <form id="unarchiveForm" action="/actions/task_action.php" method="POST" style="display:none;">
        <input type="hidden" name="action" value="unarchive">
        <input type="hidden" name="task_id" id="unarchiveTaskId">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Reveal animations
            document.querySelectorAll('.reveal-up').forEach(el => {
                setTimeout(() => el.classList.add('active'), 100);
            });

            // ── TOPBAR SEARCH LISTENER WITH EMPTY STATES ──
            document.addEventListener('pageSearch', function(e) {
                const q = e.detail.toLowerCase();

                document.querySelectorAll('.kanban-col').forEach(col => {
                    const cards = col.querySelectorAll('.task-card');
                    let matchCount = 0;

                    cards.forEach(card => {
                        const title = card.dataset.title.toLowerCase();
                        const course = (card.dataset.course || '').toLowerCase();
                        if (title.includes(q) || course.includes(q)) {
                            card.style.display = '';
                            matchCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    const kBody = col.querySelector('.k-body');
                    const defaultEmpty = kBody.querySelector('.default-empty');
                    let searchEmpty = kBody.querySelector('.search-empty');

                    // Create search-empty element if not exists
                    if (!searchEmpty) {
                        searchEmpty = document.createElement('div');
                        searchEmpty.className = 'search-empty';
                        searchEmpty.innerHTML = '<i class="fa-solid fa-magnifying-glass-minus"></i><p>No matches found.</p>';
                        kBody.appendChild(searchEmpty);
                    }

                    if (q !== '') {
                        if (matchCount === 0) {
                            searchEmpty.style.display = 'block';
                            if (defaultEmpty) defaultEmpty.style.display = 'none';
                        } else {
                            searchEmpty.style.display = 'none';
                            if (defaultEmpty) defaultEmpty.style.display = 'none';
                        }
                    } else {
                        searchEmpty.style.display = 'none';
                        if (defaultEmpty && cards.length === 0) defaultEmpty.style.display = 'flex';
                    }
                });
            });

            // Reminder Listeners
            const createVal = document.getElementById('createReminderValue');
            const createUnit = document.getElementById('createReminderUnit');
            createVal.addEventListener('input', () => updateReminderLabels(createVal, createUnit));

            const editVal = document.getElementById('editReminderValue');
            const editUnit = document.getElementById('editReminderUnit');
            editVal.addEventListener('input', () => updateReminderLabels(editVal, editUnit));
        });

        // Confirmation Logic
        let confirmCallback = null;
        function openConfirmModal(title, message, callback) {
            document.getElementById('confirmTitle').innerHTML = title;
            document.getElementById('confirmMessage').innerHTML = message;
            confirmCallback = callback;
            document.getElementById('confirmModal').classList.add('active');
        }
        function closeConfirmModal() { document.getElementById('confirmModal').classList.remove('active'); }
        document.getElementById('confirmActionBtn').addEventListener('click', () => {
            if (typeof confirmCallback === 'function') confirmCallback();
            closeConfirmModal();
        });

        // UI Functions
        function updateReminderLabels(inputEl, selectEl) {
            const val = parseInt(inputEl.value) || 0;
            const isPlural = val !== 1;
            const unitMap = { 'minutes': 'Minute', 'hours': 'Hour', 'days': 'Day', 'weeks': 'Week' };
            Array.from(selectEl.options).forEach(option => {
                const key = option.value;
                if (unitMap[key]) {
                    const label = isPlural ? unitMap[key] + 's' : unitMap[key];
                    option.textContent = `${label} before`;
                }
            });
        }

        function toggleDateInput(selectId, containerId, inputId) {
            const type = document.getElementById(selectId).value;
            const container = document.getElementById(containerId);
            const input = document.getElementById(inputId);
            if (type === 'set') { container.classList.add('show'); input.required = true; } 
            else { container.classList.remove('show'); input.required = false; input.value = ''; }
        }

        function openTaskModal() {
            document.getElementById('createTaskForm').reset();
            updateReminderLabels(document.getElementById('createReminderValue'), document.getElementById('createReminderUnit'));
            document.getElementById('newTaskModal').classList.add('active');
        }
        function closeTaskModal() { document.getElementById('newTaskModal').classList.remove('active'); }

        function openEditModal(card) {
            const d = card.dataset;
            document.getElementById('editTaskId').value = d.id;
            document.getElementById('editTaskTitle').value = d.title;
            document.getElementById('editTaskCourse').value = d.course;
            document.getElementById('editTaskStatus').value = (d.status === 'missed') ? 'pending' : d.status;
            
            const dateInput = document.getElementById('editDueDateInput');
            const typeSelect = document.getElementById('editDeadlineType');
            if (d.due) { typeSelect.value = 'set'; dateInput.value = d.due; } 
            else { typeSelect.value = 'none'; dateInput.value = ''; }
            toggleDateInput('editDeadlineType', 'editDateInputContainer', 'editDueDateInput');

            const valEl = document.getElementById('editReminderValue');
            const unitEl = document.getElementById('editReminderUnit');
            valEl.value = d.reminderValue || 0;
            unitEl.value = d.reminderUnit || 'none';
            updateReminderLabels(valEl, unitEl);

            const pInput = document.querySelector(`#editPriorityOptions input[value="${d.priority}"]`);
            if (pInput) pInput.checked = true;

            document.getElementById('editTaskModal').classList.add('active');
        }
        function closeEditModal() { document.getElementById('editTaskModal').classList.remove('active'); }

        function archiveTask() {
            openConfirmModal(
                '<i class="fa-solid fa-box-archive" style="color:var(--red);"></i> Archive Task',
                'Are you sure you want to archive this task? It will be removed from your active board.',
                () => {
                    document.getElementById('editTaskAction').value = 'archive';
                    document.getElementById('editTaskForm').submit();
                }
            );
        }

        function toggleArchive() { document.getElementById('archiveSection').classList.toggle('active'); }

        function unarchiveTask(taskId, prevLabel) {
            openConfirmModal(
                '<i class="fa-solid fa-arrow-rotate-left" style="color:var(--green);"></i> Restore Task',
                `Do you want to restore this task back to your <b>${prevLabel}</b> board?`,
                () => {
                    document.getElementById('unarchiveTaskId').value = taskId;
                    document.getElementById('unarchiveForm').submit();
                }
            );
        }
    </script>
</body>
</html>