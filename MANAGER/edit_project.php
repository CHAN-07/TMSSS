<?php
include '../config/connection.php';

$project_id = $_GET['id'] ?? null;
if (!$project_id) {
    header("Location: project.php");
    exit();
}

$project = null;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['Project_Name'] ?? '';
    $description = $_POST['Description'] ?? '';
    $start_date = $_POST['Start_Date'] ?? '';
    $due_date = $_POST['Due_Date'] ?? '';
    $status = $_POST['Status'] ?? 'Active';

    if (empty($name) || empty($start_date) || empty($due_date)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE projects SET name = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssssi", $name, $description, $start_date, $due_date, $status, $project_id);
            if ($stmt->execute()) {
                $success_message = "Project updated successfully.";
                header("Location: project.php");
                exit();
            } else {
                $error_message = "Error updating project: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Database error: " . $conn->error;
        }
    }
}

// Fetch project data
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
    <link rel="stylesheet" href="css/edit_project.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/manager_sidebar.php'; ?>

<main class="content">

    <?php include 'includes/manager_header.php'; ?>

    <div class="modal">
    <img src="https://th.bing.com/th/id/OIP.0r_Yptp1denh0qR_YHt-LAHaHa?rs=1&pid=ImgDetMain" class="close-btn" role="button" onclick="window.location.href='project.php'" />
    <h2>Edit Project</h2>
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <p>Update your project details and tasks.</p>

    <div class="tabs">
        <div class="tab active"><a href="edit_project.php?id=<?php echo $project_id; ?>">Project Details</a></div>
        <div class="tab"><a href="edit_task.php?project_id=<?php echo $project_id; ?>">Tasks</a></div>
    </div>

        <form method="POST" action="edit_project.php?id=<?php echo $project_id; ?>">
        <div class="form-group">
            <label for="Project_Name">Project Name:</label>
            <input type="text" id="Project_Name" name="Project_Name" value="<?php echo htmlspecialchars($project['name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="Description">Description:</label>
            <input type="text" id="Description" name="Description" value="<?php echo htmlspecialchars($project['description'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="Start_Date">Start Date:</label>
            <input type="date" id="Start_Date" name="Start_Date" value="<?php echo htmlspecialchars($project['start_date'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="Due_Date">Due Date:</label>
            <input type="date" id="Due_Date" name="Due_Date" value="<?php echo htmlspecialchars($project['end_date'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="Status">Status:</label>
            <select id="Status" name="Status" required>
                <option value="Active" <?php echo (isset($project['status']) && $project['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Completed" <?php echo (isset($project['status']) && $project['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Archived" <?php echo (isset($project['status']) && $project['status'] === 'Archived') ? 'selected' : ''; ?>>Archived</option>
            </select>
        </div>

        <div class="buttons">
            <div class="left-buttons">
                <button type="button" class="btn btn-close" onclick="window.location.href='project.php'">Cancel</button>
            </div>
            <div class="right-buttons">
                <button type="submit" class="btn btn-approve">Update Project</button>
            </div>
        </div>
    </form>

</div>
</main>
</body>
</html>
