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
    header("Location: department_management.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['Description']);

    if (!empty($name) && !empty($description)) {
        $stmt = $conn->prepare("UPDATE department SET department_name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);

        if ($stmt->execute()) {
            header("Location: department_management.php");
            exit;
        } else {
            $message = "Error updating department: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
} else {
    $stmt = $conn->prepare("SELECT department_name, description FROM department WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($name, $description);
    if (!$stmt->fetch()) {
        $stmt->close();
        header("Location: department_management.php");
        exit;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Department</title>
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
        <img src="https://th.bing.com/th/id/OIP.0r_Yptp1denh0qR_YHt-LAHaHa?rs=1&pid=ImgDetMain" class="close-btn" role="button" onclick="window.location.href='department_management.php'" />
        <h1>Edit Department</h1>
        <h3 class="Mcreate">Edit an existing department in the organization</h3>

        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="#">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required />
            </div>
            <div class="form-group">
                <label for="Description">Description:</label>
                <input type="text" id="Description" name="Description" value="<?php echo htmlspecialchars($description); ?>" required />
            </div>
            <button type="submit">Save changes</button>
        </form>
    </div>

</main>

</body>
</html>
