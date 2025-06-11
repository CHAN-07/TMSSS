<?php
session_start();
include '../config/connection.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($task_id > 0) {
    // Verify task belongs to the user and status is 'Pending'
    $stmt = $conn->prepare("SELECT status FROM tasks WHERE id = ? AND assignee_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row['status'] === 'Pending') {
            // Update status to 'In Progress'
            $update_stmt = $conn->prepare("UPDATE tasks SET status = 'In Progress' WHERE id = ?");
            $update_stmt->bind_param("i", $task_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
    $stmt->close();
}

// Redirect back to the task overview page
header("Location: task_overview.php");
exit();
?>
