<?php
include '../config/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = intval($_POST['task_id']);
    $reviewer_id = $_SESSION['user_id'] ?? 0;

    // Validate reviewer ID
    if ($reviewer_id <= 0) {
        die("Invalid session. Please log in again.");
    }

    // Begin transaction to ensure atomic update
    $conn->begin_transaction();

    try {
        // Step 1: Mark all pending submissions for this task as approved
        $stmt1 = $conn->prepare("
            UPDATE task_submissions 
            SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() 
            WHERE task_id = ? AND status = 'pending'
        ");
        $stmt1->bind_param("ii", $reviewer_id, $task_id);
        $stmt1->execute();

        // Step 2: Update task status and approval_status
        $stmt2 = $conn->prepare("
            UPDATE tasks 
            SET status = 'Approved', approval_status = 'view feedback', completed_date = NOW() 
            WHERE id = ?
        ");
        $stmt2->bind_param("i", $task_id);
        $stmt2->execute();

        // Optional: send notification to the assignee
        $stmt3 = $conn->prepare("
            SELECT assignee_id, title FROM tasks WHERE id = ?
        ");
        $stmt3->bind_param("i", $task_id);
        $stmt3->execute();
        $result = $stmt3->get_result();
        $task = $result->fetch_assoc();

        if ($task) {
            $assignee_id = $task['assignee_id'];
            $task_title = $task['title'];
            $message = "Your task '$task_title' has been approved.";
            
            $stmt4 = $conn->prepare("
                INSERT INTO notifications (user_id, role, message, status, fetch_notifications) 
                VALUES (?, 'employee', ?, 'unread', '')
            ");
            $stmt4->bind_param("is", $assignee_id, $message);
            $stmt4->execute();
        }

        $conn->commit();

        header("Location: supervisor_review.php?success=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing approval: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
?>
