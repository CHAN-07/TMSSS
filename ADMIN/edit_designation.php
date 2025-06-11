<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . '/../config/connection.php';

$message = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: designation_management.php");
    exit;
}

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
        $stmt = $conn->prepare("UPDATE designation SET designation_title = ?, department = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sisi", $title, $department, $description, $id);

        if ($stmt->execute()) {
            header("Location: designation_management.php");
            exit;
        } else {
            $message = "Error updating designation: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
} else {
    $stmt = $conn->prepare("SELECT designation_title, department, description FROM designation WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($title, $department, $description);
    if (!$stmt->fetch()) {
        $stmt->close();
        header("Location: designation_management.php");
        exit;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Designation</title>
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
    <img src="https://th.bing.com/th/id/OIP.0r_Yptp1denh0qR_YHt-LAHaHa?rs=1&pid=ImgDetMain" class="close-btn" role="button" onclick="window.location.href='designation_management.php'" />
      <h1>Edit Designation</h1>
      <h3 class="Mcreate">Edit job designation in the organization</h3>

      <?php if ($message): ?>
          <p><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>

      <form method="POST" action="#">
          <div class="form-group">
              <label for="title">Title:</label>
              <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required />
          </div>

          <div class="form-group">
              <label for="department">Department:</label>
              <select id="department" name="department" required>
                  <?php foreach ($departments as $dept): ?>
                      <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php if ($dept['id'] == $department) echo 'selected'; ?>>
                          <?php echo htmlspecialchars($dept['department_name']); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="form-group">
              <label for="description">Description:</label>
              <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" required />
          </div>

          <button type="submit">Save changes</button>
      </form>
  </div>

</main>

</body>
</html>
