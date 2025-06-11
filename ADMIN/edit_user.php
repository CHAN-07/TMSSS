<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . '/../config/connection.php';

$message = '';
$user = null;
$departments = [];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_management.php");
    exit;
}

$user_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $department = intval($_POST['department']);
    $role = trim($_POST['role']);
    $designation = isset($_POST['designation']) ? trim($_POST['designation']) : '';
    $status = trim($_POST['status']);

    if (!empty($username) && !empty($email) && $department > 0 && !empty($role) && !empty($designation) && !empty($status)) {
        // Check if email already exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Error: Email already exists for another user.";
            $stmt->close();
        } else {
            $stmt->close();
            // Update user
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, department_id = ?, role = ?, designation = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssisssi", $username, $email, $department, $role, $designation, $status, $user_id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: user_management.php");
                exit;
            } else {
                $message = "Error updating user: " . $stmt->error;
                $stmt->close();
            }
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, department_id, role, designation, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $stmt->close();
    header("Location: user_management.php");
    exit;
}
$stmt->close();

// Fetch departments
$result = $conn->query("SELECT id, department_name FROM department");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit User</title>
  <link rel="stylesheet" href="../css/admin.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
  <link rel="stylesheet" href="../css/edit_user.css" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>

<main class="content">

  <?php include __DIR__ . '/includess/admin_header.php'; ?>

  <div class="boxnisiya">
    <img src="https://th.bing.com/th/id/OIP.0r_Yptp1denh0qR_YHt-LAHaHa?rs=1&pid=ImgDetMain" class="close-btn" role="button" onclick="window.location.href='edit_user.php'" />
      <h1>Edit User</h1>
      <h3 class="Mcreate">Edit user in the system. Fill in all required fields.</h3>

      <?php if ($message): ?>
          <p><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>

          <form method="POST" action="#">
          <div class="form-group">
              <label for="username">Username:</label>
              <input type="text" name="username" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" required>
          </div>
          <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
          </div>
              <div class="form-group">
                  <label for="department">Department:</label>
                  <select name="department" id="department" required>
                      <?php foreach ($departments as $dept): ?>
                          <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php if ($dept['id'] == $user['department_id']) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($dept['department_name']); ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>
          <div class="form-group">
              <label for="designation">Designation:</label>
              <select name="designation" id="designation" required>
                  <option value="">Select designation</option>
              </select>
          </div>
          <div class="form-group">
              <label for="role">Role:</label>
              <select name="role" required>
                  <option value="Admin" <?php if (isset($user['role']) && $user['role'] === 'Admin') echo 'selected'; ?>>Admin</option>
                  <option value="Manager" <?php if (isset($user['role']) && $user['role'] === 'Manager') echo 'selected'; ?>>Manager</option>
                  <option value="Supervisor" <?php if (isset($user['role']) && $user['role'] === 'Supervisor') echo 'selected'; ?>>Supervisor</option>
                  <option value="Employee" <?php if (isset($user['role']) && $user['role'] === 'Employee') echo 'selected'; ?>>Employee</option>
              </select>
          </div>
          <div class="form-group">
              <label for="status">Status:</label>
              <select name="status" required>
                  <option value="active" <?php if (isset($user['status']) && $user['status'] === 'active') echo 'selected'; ?>>Active</option>
                  <option value="inactive" <?php if (isset($user['status']) && $user['status'] === 'inactive') echo 'selected'; ?>>Inactive</option>
              </select>
          </div>

          <button type="submit">Update User</button>
          </form>
  </div>

</main>

<script>
    const designations = <?php echo json_encode($conn->query("SELECT id, designation_title, department FROM designation ORDER BY designation_title")->fetch_all(MYSQLI_ASSOC)); ?>;
    const designationSelect = document.getElementById('designation');
    const departmentSelect = document.getElementById('department');

    function updateDesignationOptions() {
        const selectedDept = departmentSelect.value;
        designationSelect.innerHTML = '<option value="">Select designation</option>';
        designations.forEach(desig => {
            if (desig.department == selectedDept) {
                const option = document.createElement('option');
                option.value = desig.designation_title;
                option.textContent = desig.designation_title;
                designationSelect.appendChild(option);
            }
        });
    }

    departmentSelect.addEventListener('change', updateDesignationOptions);

    // Initialize designation options on page load
    updateDesignationOptions();

    // Set the designation dropdown to the user's current designation
    const currentDesignation = "<?php echo htmlspecialchars($user['role']); ?>";
    if (currentDesignation) {
        designationSelect.value = currentDesignation;
    }
</script>

</body>
</html>
