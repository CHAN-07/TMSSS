<?php
include __DIR__ . '/../config/connection.php';

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Update user status to inactive
    $sql = "UPDATE users SET status = 'inactive' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            // Success
        } else {
            echo "Error updating record: " . $stmt->error;
            exit();
        }
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
        exit();
    }

    // Redirect back to user management page
    header("Location: user_management.php");
    exit();
} else {
    // If no id provided, redirect back
    header("Location: user_management.php");
    exit();
}
?>
