<?php
session_start();
include '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch tasks with statuses Approved and Returned to show complete history
$sql = "SELECT tasks.id, tasks.title, projects.name AS project_name, tasks.due_date, tasks.completed_date, tasks.status,
        tf.feedback
        FROM tasks 
        LEFT JOIN projects ON tasks.project_id = projects.id 
        LEFT JOIN task_feedback tf ON tasks.id = tf.task_id
        WHERE tasks.assignee_id = ? AND tasks.status IN ('Approved', 'Returned', 'Completed')
        ORDER BY tasks.due_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Task History</title>
    <link rel="stylesheet" href="../EMPLOYEE/task_history.css" />
    <link rel="stylesheet" href="../EMPLOYEE/EmployDark.css" />
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/employee_sidebar.php'; ?>

<main class="content">

<?php include 'includes/employee_header.php'; ?>

    <!-- User List Section -->
    <section class="user-list scrollable-content">
        <h2>My Task History</h2>
        <button class="shadow" id="btnCompleted">Completed</button>
        <button class="shadow" id="btnReturned">Returned</button>
        <button class="shadow" id="btnAll">All</button>
        
        <table border="0" id="taskTable">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Project</th>
                    <th>Due Date</th>
                    <th>Completed Date</th>
                    <th>Status</th>
                    <th>Feedback</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-status="<?php echo htmlspecialchars($row['status']); ?>">
                        <td data-label='Task'>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        </td>
                        <td data-label='Project'><p class="proj"><?php echo htmlspecialchars($row['project_name']); ?></p></td>
                        <td data-label='Due Date'><?php echo htmlspecialchars(date('M d, Y', strtotime($row['due_date']))); ?></td>
                        <td data-label='Completed Date'><?php echo htmlspecialchars($row['completed_date'] ? date('M d, Y', strtotime($row['completed_date'])) : ''); ?></td>
                        <td data-label='Status'>
                            <span>
                                <?php 
                                    if ($row['status'] === 'Approved') {
                                        echo 'Approved';
                                    } else {
                                        echo htmlspecialchars($row['status']);
                                    }
                                ?>
                            </span>
                        </td>
                        <td data-label='Feedback'>
                            <?php if (!empty($row['feedback'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($row['feedback'])); ?></p>
                            <?php else: ?>
                                <p>No feedback</p>
                            <?php endif; ?>
                        </td>
                        <td data-label='Actions'>
                            <!-- Placeholder for actions -->
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; font-style: italic;">No tasks found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>     
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnCompleted = document.getElementById('btnCompleted');
    const btnReturned = document.getElementById('btnReturned');
    const btnAll = document.getElementById('btnAll');
    const rows = document.querySelectorAll('#taskTable tbody tr');

    btnCompleted.addEventListener('click', () => {
        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            row.style.display = (status === 'Approved' || status === 'Completed') ? '' : 'none';
        });
    });

    btnReturned.addEventListener('click', () => {
        rows.forEach(row => {
            row.style.display = row.getAttribute('data-status') === 'Returned' ? '' : 'none';
        });
    });

    btnAll.addEventListener('click', () => {
        rows.forEach(row => {
            row.style.display = '';
        });
    });
});
</script>
</body>
</html>
