<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . '/../config/connection.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $department = intval($_POST['department']);
    $role = trim($_POST['role']);
    $designation = trim($_POST['designation']);
    $status = trim($_POST['status']);

    if (!empty($username) && !empty($email) && !empty($password) && $department > 0 && !empty($role) && !empty($designation) && !empty($status)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Error: Email already exists.";
            $stmt->close();
        } else {
            $stmt->close();
            // Hash the password before storing
            // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Insert new user with password
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, department_id, role, designation, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssisss", $username, $email, $password, $department, $role, $designation, $status);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: user_management.php");
                exit;
            } else {
                $message = "Error adding user: " . $stmt->error;
                $stmt->close();
            }
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

$departments = [];
$result = $conn->query("SELECT id, department_name FROM department");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

$designations = [];
$result = $conn->query("SELECT id, designation_title, department FROM designation ORDER BY designation_title");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $designations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="../css/add_user.css">
    <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
    <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>

<main class="content">

  <?php include __DIR__ . '/includess/admin_header.php'; ?>

  <div class="boxnisiya">
      <img src="https://th.bing.com/th/id/OIP.0r_Yptp1denh0qR_YHt-LAHaHa?rs=1&pid=ImgDetMain" class="close-btn" role="button" onclick="window.location.href='user_management.php'" />
      <h1>Add New User</h1>
      <h3 class="Mcreate">Add a new user to the system. Fill in all required fields.</h3>

      <?php if ($message): ?>
          <p><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>

          <form method="POST" action="#">
              <div class="form-group">
                  <label for="username">Username:</label>
                  <input type="text" id="username" name="username" required>
              </div>
              <div class="form-group">
                  <label for="email">Email:</label>
                  <input type="email" id="email" name="email" required>
              </div>
              <div class="form-group">
                  <label for="password">Password:</label>
                  <input type="password" id="password" name="password" required>
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
                  <label for="designation">Designation:</label>
                  <select id="designation" name="designation" required>
                      <option value="">Select designation</option>
                  </select>
              </div>
              <div class="form-group">
                  <label for="role">Role:</label>
                  <select id="role" name="role" required>
                      <option value="Admin">Admin</option>
                      <option value="Manager">Manager</option>
                      <option value="Supervisor">Supervisor</option>
                      <option value="Employee">Employee</option>
                  </select>
              </div>
              <div class="form-group">
                  <label for="status">Status:</label>
                  <select id="status" name="status" required>
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                  </select>
              </div>
              <button type="submit">Add User</button>
          </form>
  </div>

</main>

<script>
    const designations = <?php echo json_encode($designations); ?>;
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
</script>

</body>
</html>
