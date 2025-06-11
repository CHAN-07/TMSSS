<?php
include '../config/connection.php';

$project_id = $_GET['project_id'] ?? null;
$task_id = $_GET['task_id'] ?? null;

$tasks = [];
$current_task = [];
$assignees = [];

if ($project_id) {
    $stmt = $conn->prepare("SELECT id, title FROM tasks WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    $stmt->close();
}

if ($task_id) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_task = $result->fetch_assoc();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'employee' AND status = 'Active'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $assignees[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="css/edit_project.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/manager_sidebar.php'; ?>

<main class="content">
    <?php include 'includes/manager_header.php'; ?>

    <div class="modal">
        <div class="close-icon" onclick="window.location.href='project.php'" >×</div>
        <h2>Edit Task</h2>
        <p>Update your project details and tasks.</p>

    <div class="tabs">
        <div class="tab"><a href="edit_project.php?id=<?php echo $project_id; ?>">Project Details</a></div>
        <div class="tab active"><a href="edit_task.php?project_id=<?php echo $project_id; ?>">Tasks</a></div>
    </div>

        <form method="POST" action="edit_task.php?project_id=<?php echo htmlspecialchars($project_id); ?>&task_id=<?php echo htmlspecialchars($task_id); ?>">
            <div class="form-group">
                <label for="Task_title">Task title:</label>
                <select id="Task_title" name="Task_title" required>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?php echo htmlspecialchars($task['title']); ?>" <?php echo ($current_task && $current_task['title'] == $task['title']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($task['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="Description">Description:</label>
                <input type="text" id="Description" name="Description" value="<?php echo htmlspecialchars($current_task['description'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="Assignee">Assignee:</label>
                <select id="Assignee" name="Assignee" required>
                    <?php foreach ($assignees as $assignee): ?>
                        <option value="<?php echo htmlspecialchars($assignee['id']); ?>" <?php echo (isset($current_task['assignee_id']) && $current_task['assignee_id'] == $assignee['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($assignee['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="Start_Date">Start Date:</label>
                <input type="date" id="Start_Date" name="Start_Date" value="<?php echo htmlspecialchars($current_task['start_date'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="Due_Date">Due Date:</label>
                <input type="date" id="Due_Date" name="Due_Date" value="<?php echo htmlspecialchars($current_task['due_date'] ?? ''); ?>" required>
            </div>

            <div class="buttons">
                <div class="left-buttons">
                    <button type="button" class="btn btn-close" onclick="window.location.href='edit_project.php?project_id=<?php echo htmlspecialchars($project_id); ?>'">Cancel</button>
                </div>
                <div class="right-buttons">
                    <button type="submit" class="btn btn-approve">Update Task</button>
                </div>
            </div>
        </form>
    </div>
</main>
</body>
</html>
