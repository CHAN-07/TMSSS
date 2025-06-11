<?php
session_start();
// include __DIR__ . '/../ADMIN/notification_helper.php';
// include __DIR__ . '/includes/employee_sidebar.php';
// include __DIR__ . '/includes/employee_header.php';

// Assuming user_id and role are stored in session
$user_id = $_SESSION['user_id'] ?? null;
$role = 'Employee';

$notifications = [];
if ($user_id) {
    // Sample query to fetch tasks with supervisor feedback or approval for the logged-in employee
    $sql_notifications = "SELECT task_name, supervisor_feedback, approval_status, updated_at 
                          FROM tasks 
                          WHERE employee_id = ? AND (supervisor_feedback IS NOT NULL OR approval_status = 'approved')
                          ORDER BY updated_at DESC";
    $stmt = $conn->prepare($sql_notifications);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_notifications = $stmt->get_result();

    while ($row = $result_notifications->fetch_assoc()) {
        $message = "Task '" . htmlspecialchars($row['task_name']) . "' ";
        if (!empty($row['supervisor_feedback'])) {
            $message .= "has feedback: " . htmlspecialchars($row['supervisor_feedback']) . ". ";
        }
        if ($row['approval_status'] === 'approved') {
            $message .= "has been approved by supervisor.";
        }
        $notifications[] = [
            'message' => $message,
            'created_at' => $row['updated_at']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <link rel="stylesheet" href="../EMPLOYEE/css/employee_notification.css">
    <link rel="stylesheet" href="css/global/employee_sidebar.css">
    <link rel="stylesheet" href="css/global/employee_header.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/employee_sidebar.php'; ?>

<main class="content">

<?php include 'includes/employee_header.php'; ?>

    <div class="box">
        <!-- Title or header for the notification box -->
        <div class="word">Notifications</div>

        <!-- Scrollable container for notification items -->
        <div class="minib">
            <?php if (empty($notifications)): ?>
                <p>No notifications found.</p>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="box1">
                        <div class="user-role-row">
                            <span class="U">Notification</span>
                        </div>
                        <p class="G"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <p class="Eg"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Buttons or links at the bottom -->
        <div class="mark-read">
            <button>Mark all as read</button>
        </div>

        <div class="view-all">
            <a href="#">View all notifications</a>
        </div>
    </div>
</main>
</body>
</html>
