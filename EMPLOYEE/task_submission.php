<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

function sendTaskToSupervisor($conn, $task_id, $user_id, $username) {
    // Update task status to Submitted and approval_status to pending
    $update_stmt = $conn->prepare("UPDATE tasks SET status = 'Submitted', approval_status = 'pending' WHERE id = ?");
    $update_stmt->bind_param("i", $task_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Notify all supervisors about the submitted task
    $supervisor_stmt = $conn->prepare("SELECT id FROM users WHERE role = 'supervisor' AND status = 'active'");
    if ($supervisor_stmt) {
        $supervisor_stmt->execute();
        $supervisor_result = $supervisor_stmt->get_result();
        while ($supervisor = $supervisor_result->fetch_assoc()) {
            $message = "Task ID $task_id submitted by $username";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            if ($notif_stmt) {
                $notif_stmt->bind_param("is", $supervisor['id'], $message);
                $notif_stmt->execute();
                $notif_stmt->close();
            }
        }
        $supervisor_stmt->close();
    }
}

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$user_id = $_SESSION['user_id'];

$task = null;
$error_message = '';
$success_message = '';

if ($task_id > 0) {
       
    $stmt = $conn->prepare("SELECT t.title, t.description, p.name AS project_name, t.due_date, t.status, t.approval_status
                            FROM tasks t
                            LEFT JOIN projects p ON t.project_id = p.id
                            WHERE t.id = ? AND t.assignee_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $task = $result->fetch_assoc();

        // Fetch supervisor feedback if any
        $stmt_feedback = $conn->prepare("SELECT feedback FROM task_feedback WHERE task_id = ?");
        $stmt_feedback->bind_param("i", $task_id);
        $stmt_feedback->execute();
        $result_feedback = $stmt_feedback->get_result();
        if ($result_feedback->num_rows === 1) {
            $task['feedback'] = $result_feedback->fetch_assoc()['feedback'];
        } else {
            $task['feedback'] = '';
        }
        $stmt_feedback->close();

    } else {
        $error_message = "Task not found or you do not have permission to view it.";
    }
    $stmt->close();
} else {
    $error_message = "Invalid task ID.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_notes = trim($_POST['submission_notes'] ?? '');

    if ($task_id > 0) {
        // Save submission notes to a new table or update task status as needed
        // For simplicity, let's update the task status to 'Submitted' and save notes in a new table 'task_submissions'

        // Insert submission notes
        $stmt = $conn->prepare("INSERT INTO task_submissions (task_id, user_id, submission_text, submitted_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $task_id, $user_id, $submission_notes);
        if ($stmt->execute()) {
            // Update task status to Submitted and set approval_status to pending
            $update_stmt = $conn->prepare("UPDATE tasks SET status = 'Submitted', approval_status = 'pending' WHERE id = ?");
            $update_stmt->bind_param("i", $task_id);
            if (!$update_stmt->execute()) {
                error_log("Failed to update task status and approval_status: " . $update_stmt->error);
            } else {
                error_log("Updated task id $task_id, affected rows: " . $update_stmt->affected_rows);
            }
            $update_stmt->close();

            // Debug: Log current task status and approval_status
            $debug_stmt = $conn->prepare("SELECT status, approval_status FROM tasks WHERE id = ?");
            $debug_stmt->bind_param("i", $task_id);
            $debug_stmt->execute();
            $debug_result = $debug_stmt->get_result();
            if ($debug_result->num_rows === 1) {
                $debug_row = $debug_result->fetch_assoc();
                error_log("DEBUG: Task ID $task_id status: " . $debug_row['status'] . ", approval_status: " . $debug_row['approval_status']);
            }
            $debug_stmt->close();

            $success_message = "Task submitted successfully.";

            // Notify all supervisors about the submitted task
            $supervisor_stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'supervisor' AND status = 'active'");
            if ($supervisor_stmt) {
                $supervisor_stmt->execute();
                $supervisor_result = $supervisor_stmt->get_result();
                while ($supervisor = $supervisor_result->fetch_assoc()) {
                    $message = "Task '" . $task['title'] . "' submitted by " . $_SESSION['username'];
                    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    if ($notif_stmt) {
                        $notif_stmt->bind_param("is", $supervisor['id'], $message);
                        $notif_stmt->execute();
                        $notif_stmt->close();
                    }
                }
                $supervisor_stmt->close();
            }

            // Refresh task details
            $stmt = $conn->prepare("SELECT t.title, t.description, p.name AS project_name, t.due_date, t.status
                                    FROM tasks t
                                    LEFT JOIN projects p ON t.project_id = p.id
                                    WHERE t.id = ? AND t.assignee_id = ?");
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $task = $result->fetch_assoc();
            }
            $stmt->close();
        } else {
            $error_message = "Failed to submit task.";
        }
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
    <title>Task Submission</title>
    <link rel="stylesheet" href="css/task_submission.css" />
    <link rel="stylesheet" href="css/global/employee_sidebar.css" />
    <link rel="stylesheet" href="css/global/employee_header.css" />
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/employee_sidebar.php'; ?>

<main class="content">

<?php include 'includes/employee_header.php'; ?>

<div class="modal">
    <div class="close-icon" onclick="window.location.href='task_overview.php'">×</div>
    <h1>Task Submission</h1>

    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php elseif ($task): ?>
        <div class="badges"> 
            <div class="choice">
                <p class="<?php 
                    if ($task['approval_status'] === 'approved') echo 'Green';
                    elseif ($task['approval_status'] === 'returned') echo 'Red';
                    elseif ($task['approval_status'] === 'pending') echo 'Yellow';
                    else echo 'Blue';
                ?>">
                    <?php echo htmlspecialchars(ucfirst($task['approval_status'] ?? 'pending')); ?>
                </p>
                <div class="title">
                    <h2><?php echo htmlspecialchars($task['title']); ?></h2>
                    <h3><?php echo htmlspecialchars($task['project_name']); ?></h3>
                </div>
                <p class="description">
                    <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                </p>
                <p class="due">Due: <?php echo htmlspecialchars(date('M d, Y', strtotime($task['due_date']))); ?></p>
            </div>
        </div>

        

        <?php if ($success_message): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($task['feedback'])): ?>
            <div class="section">
                <h4>Supervisor Feedback</h4>
                <p><?php echo nl2br(htmlspecialchars($task['feedback'])); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="task_submission.php?task_id=<?php echo $task_id; ?>">
            <div class="section">
                <h4>Submission notes</h4>
                <textarea name="submission_notes" rows="3" placeholder="Write notes here..."></textarea>
            </div>

            <div class="attachments-container">
                <h3>Attachments</h3>
                <!-- Attachment upload UI can be implemented here -->
                <label class="add-attachment" for="file-upload">
                    + Add attachment
                </label>
                <input id="file-upload" type="file" multiple onchange="handleFiles(this.files)">
            </div>

            <div class="buttons">
                <div class="left-buttons">
                    <button type="button" class="btn btn-close" onclick="window.location.href='task_overview.php'">Close</button>
                </div>
                <div class="right-buttons">
                    <button type="submit" class="btn btn-approve">Submit Task</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
  function removeItem(button) {
    button.closest('.file-item').remove();
  }

  function handleFiles(files) {
    const container = document.querySelector('.attachments-container');
    for (const file of files) {
      const size = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
      const div = document.createElement('div');
      div.className = 'file-item';
      div.innerHTML = <span>${file.name} <small>(${size})</small></span><button onclick="removeItem(this)">✕</button>;
      container.insertBefore(div, document.querySelector('.add-attachment'));
    }
  }
</script>

</main>
</body>
</html>