<?php
include '../config/connection.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;

$sql = "SELECT t.id, t.title, t.due_date, t.status, t.approval_status, t.priority, 
               p.name AS project_name, ts.submitted_at, tf.feedback
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        LEFT JOIN task_submissions ts ON t.id = ts.task_id
        LEFT JOIN task_feedback tf ON t.id = tf.task_id
        WHERE t.assignee_id = ?
        ORDER BY t.due_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Overview</title>
    <link rel="stylesheet" href="../EMPLOYEE/css/task_overview.css" />
    <link rel="stylesheet" href="css/global/employee_sidebar.css" />
    <link rel="stylesheet" href="css/global/employee_header.css" />
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/employee_sidebar.php'; ?>

<main class="content">
    <?php include 'includes/employee_header.php'; ?>

    <section class="user-list scrollable-content">
        <h2>My Task</h2>
        <div class="bastaword-wrapper">
            <h3 class="bastaword">View and manage your assigned tasks</h3>
        </div>

        <div class="search-bar">
            <input id="departmentSearch" class="ins" type="text" placeholder="Search departments...">
            <select class="choice" id="role" name="role" required>
                <option value="All">All Task</option>
                <option value="Manager">Manager</option>
                <option value="Supervisor">Supervisor</option>
                <option value="Employee">Employee</option>
            </select>
        </div>

        <table border="0">
            <tr>
                <th>Task</th>
                <th>Project</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Actions</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td data-label='Task'>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p>Submitted: 
                                <?php echo $row['submitted_at'] 
                                    ? htmlspecialchars(date('M d, h:i A', strtotime($row['submitted_at']))) 
                                    : 'Not submitted'; ?>
                            </p>
                        </td>
                        <td data-label='Project'>
                            <p class="proj"><?php echo htmlspecialchars($row['project_name']); ?></p>
                        </td>
                        <td data-label='Due Date'><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td data-label='Status'><strong><?php echo htmlspecialchars($row['status']); ?></strong></td>
                        <td data-label='Priority'>
                            <span class="<?php echo strtolower($row['priority']); ?>">
                                <?php echo htmlspecialchars($row['priority']); ?>
                            </span>
                        </td>
                        <td data-label='Actions'>
                            <?php
                            // Implement action buttons based on status and approval_status
                            $status = $row['status'];
                            $approval_status = $row['approval_status'] ?? 'Pending';

                            if ($status === 'Pending') {
                                echo '<a href="start_task.php?task_id=' . $row['id'] . '" class="choice">Start Task</a>';
                            } elseif ($status === 'In Progress') {
                                echo '<a href="task_submission.php?task_id=' . $row['id'] . '" class="choice">Submit Task</a>';
                            } elseif ($status === 'Submitted') {
                                echo '<button class="choice" disabled>Waiting for Approval</button>';
                            } elseif ($status === 'Approved') {
                                echo '<a href="feedback.php?task_id=' . $row['id'] . '" class="choice">View Feedback</a>';
                            } elseif ($status === 'Returned') {
                                echo '<a href="feedback.php?task_id=' . $row['id'] . '" class="choice">View Feedback</a>';
                            } else {
                                echo '<button class="choice" disabled>' . htmlspecialchars($status) . '</button>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No tasks assigned.</td></tr>
            <?php endif; ?>
        </table>
    </section>     
</main>
</body>
</html>
