<?php
date_default_timezone_set('Asia/Manila');

$backend_root = dirname(__DIR__); 
require_once $backend_root . '/api/send_mail.php'; 
require_once $backend_root . '/includes/db.php';

// 1. Grab your dynamic APP_URL from the environment
$app_url = getenv('APP_URL') ?: 'http://localhost';

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.email, u.full_name 
        FROM tasks t
        JOIN users u ON t.user_id = u.id
        WHERE t.reminder_sent = 0 
        AND t.reminder_unit != 'none'
        AND t.status IN ('pending', 'in_progress')
    ");
    $stmt->execute();
    $allPotentialTasks = $stmt->fetchAll();

    foreach ($allPotentialTasks as $task) {
        $dueDate = new DateTime($task['due_date']);
        $reminderValue = $task['reminder_value'];
        $reminderUnit = $task['reminder_unit'];

        $reminderTime = clone $dueDate;
        $reminderTime->modify("-$reminderValue $reminderUnit");
        
        $now = new DateTime();
        
        if ($now >= $reminderTime) {
            $subject = "TalaAral Reminder: " . $task['title'];
            
            // 2. Use the $app_url variable in the link below
            $body = "
            <div style='background-color: #f4f7f9; padding: 20px; font-family: sans-serif; color: #1e293b;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;'>
                    <div style='background: #0f172a; padding: 20px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 20px;'>TalaAral</h1>
                    </div>
                    <div style='padding: 30px;'>
                        <h2 style='margin-top: 0; color: #0f172a;'>Hello, {$task['full_name']}!</h2>
                        <p style='font-size: 16px; color: #475569;'>Your task is due soon. Here are the details:</p>
                        
                        <div style='background: #f8fafc; border-radius: 6px; padding: 20px; border-left: 4px solid #0052FF; margin: 20px 0;'>
                            <p style='margin: 0 0 10px 0;'><strong>Task:</strong> {$task['title']}</p>
                            <p style='margin: 0 0 10px 0;'><strong>Course:</strong> " . ($task['course'] ?: 'General') . "</p>
                            <p style='margin: 0;'><strong>Deadline:</strong> " . $dueDate->format('M j, Y - g:i A') . "</p>
                        </div>

                        <div style='text-align: center; margin-top: 25px;'>
                            <a href='{$app_url}/views/tasks.php' style='background: #0052FF; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>View Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>";

            if (sendTalaAralEmail($task['email'], $subject, $body)) {
                $update = $pdo->prepare("UPDATE tasks SET reminder_sent = 1 WHERE task_id = ?");
                $update->execute([$task['task_id']]);
            }
        }
    }

    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] Check completed. Tasks found: " . count($allPotentialTasks) . "\n";

} catch (Exception $e) {
    error_log("TalaAral Cron Error: " . $e->getMessage());
}