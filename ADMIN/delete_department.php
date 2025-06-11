<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . '/../config/connection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM department WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: department_management.php");
        exit;
    } else {
        $stmt->close();
        echo "Error deleting department: " . $conn->error;
    }
} else {
    header("Location: department_management.php");
    exit;
}
?>
