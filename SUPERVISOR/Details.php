<?php
include '../config/connection.php';

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$task = null;
$submission = null;
$error_message = '';
$success_message = '';

// Handle form submission for approval or request changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $task_id > 0) {
    $action = $_POST['action'] ?? '';
    $feedback = trim($_POST['feedback'] ?? '');

    // Fetch assignee id for notification
    $assignee_id = null;
    $stmt = $conn->prepare("SELECT assignee_id FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $assignee_id = $row['assignee_id'];
    }
    $stmt->close();

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE tasks SET status = 'Approved', approval_status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $stmt->close();

        // Notify employee about approval
        if ($assignee_id) {
            $message = "Your task ID $task_id has been approved by the supervisor.";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            if ($notif_stmt) {
                $notif_stmt->bind_param("is", $assignee_id, $message);
                $notif_stmt->execute();
                $notif_stmt->close();
            }
        }

        $success_message = "Task approved successfully.";
    } elseif ($action === 'request_changes') {
        if (empty($feedback)) {
            $error_message = "Feedback is required when requesting changes.";
        } else {
            $stmt = $conn->prepare("UPDATE tasks SET status = 'Returned', approval_status = 'returned' WHERE id = ?");
            $stmt->bind_param("i", $task_id);
            $stmt->execute();
            $stmt->close();

            // Save feedback in a new table or update existing feedback column
            $stmt = $conn->prepare("INSERT INTO task_feedback (task_id, feedback, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE feedback = VALUES(feedback), created_at = NOW()");
            $stmt->bind_param("is", $task_id, $feedback);
            $stmt->execute();
            $stmt->close();

            // Notify employee about change request
            if ($assignee_id) {
                $message = "Your task ID $task_id has been returned with feedback from the supervisor.";
                $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                if ($notif_stmt) {
                    $notif_stmt->bind_param("is", $assignee_id, $message);
                    $notif_stmt->execute();
                    $notif_stmt->close();
                }
            }

            $success_message = "Change request sent successfully.";
        }
    }
}

// Fetch task details and submission notes
if ($task_id > 0) {
    // Removed join on t.assigned_by as the column does not exist
    $stmt = $conn->prepare("SELECT t.title, t.description, t.due_date, t.status, t.priority, p.name AS project_name, u.username AS assigned_to
                            FROM tasks t
                            LEFT JOIN projects p ON t.project_id = p.id
                            LEFT JOIN users u ON t.assignee_id = u.id
                            WHERE t.id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $task = $result->fetch_assoc();
    } else {
        $error_message = "Task not found.";
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT notes, submitted_at FROM task_submissions WHERE task_id = ? ORDER BY submitted_at DESC LIMIT 1");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $submission = $result->fetch_assoc();
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT feedback FROM task_feedback WHERE task_id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback_row = $result->fetch_assoc();
    $existing_feedback = $feedback_row ? $feedback_row['feedback'] : '';
    $stmt->close();
} else {
    $error_message = "Invalid task ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Task Details</title>
    <link rel="stylesheet" href="css/Details.css" />
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/supervisor_sidebar.php'; ?>

<main class="content">

<?php include 'includes/supervisor_header.php'; ?>

<div class="modal">
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php elseif ($task): ?>
        <h2><?php echo htmlspecialchars($task['title']); ?></h2>

        <div class="badges">
            <div class="choice"><?php echo htmlspecialchars($task['project_name']); ?></div>
            <div class="<?php echo strtolower($task['status']); ?>"><?php echo htmlspecialchars($task['status']); ?></div>
            <div class="<?php echo strtolower($task['priority']); ?>"><?php echo htmlspecialchars($task['priority']); ?></div>
        </div>

        <div class="section">
            <h4>Description</h4>
            <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
        </div>

        <div class="row">
            <div class="box">
                <div class="icon-label">Assigned to:</div>
                <p><?php echo htmlspecialchars($task['assigned_to']); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="box">
                <div class="icon-label">Due Date:</div>
                <p><?php echo htmlspecialchars(date('M d, Y', strtotime($task['due_date']))); ?></p>
            </div>
            <div class="box">
                <div class="icon-label">Submitted At:</div>
                <p><?php echo $submission ? htmlspecialchars(date('M d, Y, h:i A', strtotime($submission['submitted_at']))) : 'N/A'; ?></p>
            </div>
        </div>

        <div class="section">
            <h4>Submission Notes</h4>
            <p><?php echo $submission ? nl2br(htmlspecialchars($submission['notes'])) : 'No submission notes.'; ?></p>
        </div>

        <div class="section">
            <h4>Feedback</h4>
            <form method="POST" action="Details.php?task_id=<?php echo $task_id; ?>">
                <textarea name="feedback" rows="3" placeholder="Write feedback here..."><?php echo htmlspecialchars($existing_feedback); ?></textarea>
                <div class="note">Feedback is required when requesting changes</div>
                <div class="buttons">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-close" onclick="window.location.href='task_review.php'">Close</button>
                    </div>
                    <div class="right-buttons">
                        <button type="submit" name="action" value="request_changes" class="btn btn-request">Request Changes</button>
                        <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($success_message): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    <?php else: ?>
        <p>Task not found.</p>
    <?php endif; ?>
</div>

</main>
</body>
</html>
