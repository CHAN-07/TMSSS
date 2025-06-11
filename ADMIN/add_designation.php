<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . '/../config/connection.php';

$message = '';

$departments = [];
$result = $conn->query("SELECT id, department_name FROM department");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $department = intval($_POST['department']);
    $description = trim($_POST['description']);

    if (!empty($title) && $department > 0 && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO designation (designation_title, department, description, created_at) VALUES (?, ?, ?, CURDATE())");
        $stmt->bind_param("sis", $title, $department, $description);

        if ($stmt->execute()) {
            header("Location: designation_management.php");
            exit;
        } else {
            $message = "Error adding designation: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Designation</title>
  <link rel="stylesheet" href="../css/edit_user.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>

<main class="content">

  <?php include __DIR__ . '/includess/admin_header.php'; ?>

  <div class="boxnisiya">
    <div class="header-container">
      <h1>Add Designation</h1>
      <img src="https://cdn0.iconfinder.com/data/icons/evericons-16px/16/x-128.png" class="Xbutton">
    </div>
      <h3 class="Mcreate">Add a new job designation to the organization</h3>

      <?php if ($message): ?>
          <p><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>

      <form method="POST" action="#">
          <div class="form-group">
              <label for="title">Title:</label>
              <input type="text" id="title" name="title" required />
          </div>

          <div class="form-group">
              <label for="department">Department:</label>
              <select id="department" name="department" required>
                  <?php foreach ($departments as $dept): ?>
                      <option value="<?php echo htmlspecialchars($dept['id']); ?>">
                          <?php echo htmlspecialchars($dept['department_name']); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="form-group">
              <label for="description">Description:</label>
              <input type="text" id="description" name="description" required />
          </div>

          <button type="submit">Add Designation</button>
      </form>
  </div>

</main>

</body>
</html>
