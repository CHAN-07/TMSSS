<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$user_id = $_SESSION['user_id'];

$feedback = '';
$status = '';
$error_message = '';

if ($task_id > 0) {
    // Verify task belongs to user
    $stmt = $conn->prepare("SELECT status FROM tasks WHERE id = ? AND assignee_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $status = $row['status'];

        // Fetch feedback
        $fb_stmt = $conn->prepare("SELECT feedback FROM task_feedback WHERE task_id = ?");
        $fb_stmt->bind_param("i", $task_id);
        $fb_stmt->execute();
        $fb_result = $fb_stmt->get_result();

        if ($fb_result->num_rows === 1) {
            $fb_row = $fb_result->fetch_assoc();
            $feedback = $fb_row['feedback'];
        }

        $fb_stmt->close();
    } else {
        $error_message = "Task not found or you do not have permission to view feedback.";
    }

    $stmt->close();
} else {
    $error_message = "Invalid task ID.";
}

// Handle "Start Task Again"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_again'])) {
    if ($task_id > 0) {
        $update_stmt = $conn->prepare("UPDATE tasks SET status = 'In Progress' WHERE id = ? AND assignee_id = ?");
        $update_stmt->bind_param("ii", $task_id, $user_id);
        if ($update_stmt->execute()) {
            header("Location: task_overview.php");
            exit();
        } else {
            $error_message = "Failed to update task status.";
        }
        $update_stmt->close();
    } else {
        $error_message = "Invalid task ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Task Feedback</title>
    <link rel="stylesheet" href="css/feedback.css" />
    <link rel="stylesheet" href="css/global/employee_sidebar.css" />
    <link rel="stylesheet" href="css/global/employee_header.css" />
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/employee_sidebar.php'; ?>

<main class="content">

<?php include 'includes/employee_header.php'; ?>

<section class="feedback-section">
    <h1>Task Feedback</h1>

    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php else: ?>
        <div class="feedback-content">
            <h2>Task Status: <?php echo htmlspecialchars($status); ?></h2>
            <p><strong>Feedback:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($feedback)); ?></p>

            <?php if ($status === 'Returned'): ?>
                <form method="POST" action="feedback.php?task_id=<?php echo $task_id; ?>">
                    <button type="submit" name="start_again" class="btn btn-primary">Start Task Again</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

</main>
</body>
</html>
?>
