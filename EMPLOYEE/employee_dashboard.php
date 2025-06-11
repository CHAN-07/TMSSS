<?php
session_start();
include '../config/connection.php';

$user_id = $_SESSION['user_id'] ?? 0;

// Query to count assigned tasks for the logged-in user (all tasks regardless of due date)
$assignedTaskCount = 0;
$inProgressTaskCount = 0;
$completedTaskCount = 0;
if ($user_id) {
    // Assigned tasks count
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM tasks WHERE assignee_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $assignedTaskCount = $row['count'];
    }
    $stmt->close();

    // In Progress tasks count
    $stmt2 = $conn->prepare("SELECT COUNT(*) AS count FROM tasks WHERE assignee_id = ? AND status = 'In Progress'");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($result2 && $row2 = $result2->fetch_assoc()) {
        $inProgressTaskCount = $row2['count'];
    }
    $stmt2->close();

    // Completed tasks count for current month
    $stmt3 = $conn->prepare("SELECT COUNT(*) AS count FROM tasks WHERE assignee_id = ? AND status = 'Completed' AND MONTH(due_date) = MONTH(CURRENT_DATE()) AND YEAR(due_date) = YEAR(CURRENT_DATE())");
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    if ($result3 && $row3 = $result3->fetch_assoc()) {
        $completedTaskCount = $row3['count'];
    }
    $stmt3->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="css/employee_dashboard.css">
    <link rel="stylesheet" href="css/global/employee_sidebar.css">
    <link rel="stylesheet" href="css/global/employee_header.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/employee_sidebar.php'; ?>

<main class="content">

<?php include 'includes/employee_header.php'; ?>
    

    <h1 class="hello-admin">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    <section class="dashboard-cards">
         <div class="card">
            <h2>Assigned Task</h2>
            <h3><?php echo $assignedTaskCount; ?> New Task<?php echo $assignedTaskCount !== 1 ? 's' : ''; ?></h3>
            <p><?php echo $assignedTaskCount; ?></p>
            <img src="https://cdn.iconfinder.com/stored_data/2147713/128/png?token=1748084973-9mgMiJrTqC8zrG2S7ascvpMSpEfkMFuYqLCyQ%2FhrNRU%3D">
        </div>
        <div class="card">
            <h2>In Progress</h2>
            <h3><?php echo $inProgressTaskCount; ?> Due Today</h3>
            <p><?php echo $inProgressTaskCount; ?></p>
            <img src="https://cdn.iconfinder.com/stored_data/2147716/128/png?token=1748100684-J3ByeJCiLjjCLq7Ycc%2FnUTs4VXXSk4pjtxEONqBK7EQ%3D">
        </div>
        <div class="card">
            <h2>Completed</h2>
            <h3>This month</h3>
            <p><?php echo $completedTaskCount; ?></p>
            <img  class="red" src="https://cdn.iconfinder.com/stored_data/2147719/128/png?token=1748100684-dOzYAun8bVBPiO7zD%2BZ9yWBx3JTvzXc%2BW%2Fj5%2FX01y7M%3D">  
         </div>
    </section>  

   <!-- Recent User Activity -->
<div class="recent-activity-wrapper">
<section class="recent-activity-panel">
    <h2><img src="https://cdn.iconfinder.com/stored_data/2147713/128/png?token=1748084973-9mgMiJrTqC8zrG2S7ascvpMSpEfkMFuYqLCyQ%2FhrNRU%3D">Upcoming deadline</h2>
    <?php
    if ($user_id) {
        $stmt = $conn->prepare("SELECT title, due_date, status FROM tasks WHERE assignee_id = ? AND due_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 3 DAY) ORDER BY due_date ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($task = $result->fetch_assoc()) {
                $statusClass = '';
                switch ($task['status']) {
                    case 'In Progress':
                        $statusClass = 'status in-progress';
                        break;
                    case 'Returned':
                        $statusClass = 'Red1';
                        break;
                    case 'Not Started':
                        $statusClass = 'grey';
                        break;
                    default:
                        $statusClass = '';
                }
                echo '<div class="task-container">';
                echo '<div class="task-text">';
                echo '<h2>' . htmlspecialchars($task['title']) . '</h2>';
                // Removed project_name display as column does not exist
                echo '</div>';
                echo '<div class="task-meta">';
                echo '<p class="due-date">Due: ' . date("M d", strtotime($task['due_date'])) . '</p>';
                echo '<div class="' . $statusClass . '">' . htmlspecialchars($task['status']) . '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No upcoming deadlines.</p>';
        }
        $stmt->close();
    }
    ?>
</section>


</div>
</main>

</body>
</html>
