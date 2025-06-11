<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . '/../config/connection.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (!empty($name) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO department (department_name, description, created_at) VALUES (?, ?, CURDATE())");
        $stmt->bind_param("ss", $name, $description);

        if ($stmt->execute()) {
            header("Location: department_management.php");
            exit;
        } else {
            $message = "Error adding department: " . $stmt->error;
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
  <title>Add Department</title>
  <link rel="stylesheet" href="../css/edit_user.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>

<main class="content">

  <?php include __DIR__ . '/includess/admin_header.php'; ?>

  <div class="boxnisiya">
    <div class="header-container">
      <h1>Add Department</h1>
      <img src="https://cdn0.iconfinder.com/data/icons/evericons-16px/16/x-128.png" class="Xbutton">
    </div>
      <h3 class="Mcreate">Add a new department to the organization</h3>

      <?php if ($message): ?>
          <p><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>

      <form method="POST" action="#">
          <div class="form-group">
              <label for="name">Department Name:</label>
              <input type="text" id="name" name="name" required />
          </div>
          <div class="form-group">
              <label for="description">Description:</label>
              <input type="text" id="description" name="description" required />
          </div>
          <button type="submit">Add Department</button>
      </form>
  </div>

</main>

</body>
</html>
