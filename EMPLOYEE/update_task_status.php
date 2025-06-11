<?php
session_start();
include '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($task_id <= 0 || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }

    // Verify that the task belongs to the logged-in user
    $stmt = $conn->prepare("SELECT assignee_id FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit();
    }
    $row = $result->fetch_assoc();
    if ($row['assignee_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    // Update the task status
    $update_stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $task_id);
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
