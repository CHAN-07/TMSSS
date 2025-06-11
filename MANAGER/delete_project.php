<?php
include '../config/connection.php';

if (isset($_GET['id'])) {
    $project_id = intval($_GET['id']);

    // Delete the project
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $stmt->close();
    }

    // Optionally, delete related tasks if needed
    // $stmt = $conn->prepare("DELETE FROM tasks WHERE project_id = ?");
    // if ($stmt) {
    //     $stmt->bind_param("i", $project_id);
    //     $stmt->execute();
    //     $stmt->close();
    // }
}

header("Location: project.php");
exit();
?>
