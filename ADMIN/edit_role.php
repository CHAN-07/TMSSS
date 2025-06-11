<?php
require_once __DIR__ . '/../config/connection.php';

// Initialize variables
$selected_user = null;
$selected_role = null;
$message = "";

// Handle POST request to update role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $conn->real_escape_string($_POST['role']);
    $update_sql = "UPDATE users SET role = '$new_role' WHERE id = $user_id";
    if ($conn->query($update_sql) === TRUE) {
        header("Location: edit_role.php?user_id=$user_id&updated=1&success=1");
        exit();
    } else {
        $message = "Error updating role: " . $conn->error;
    }
}

// Get selected user if available
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $user_sql = "SELECT id, username, role FROM users WHERE id = $user_id LIMIT 1";
    $user_result = $conn->query($user_sql);
    if ($user_result && $user_result->num_rows > 0) {
        $selected_user = $user_result->fetch_assoc();
        $selected_role = $selected_user['role'];
    }
}

// Get all users for display
$sql = "SELECT users.id, users.username, users.email, users.role AS role_name,
        IFNULL(department.department_name, 'No Department') AS department_name
        FROM users
        LEFT JOIN department ON users.department_id = department.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Role Assignment - Edit</title>
  <link rel="stylesheet" href="../CSS/edit_role.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>

<main class="content">
  <?php include __DIR__ . '/includess/admin_header.php'; ?>

  <div class="box">
    <p class="word">Role Assignment</p>
    <p class="word1">Assigned Roles and Permission to users in the system.</p>
    <p class="word2">Select User</p>

    <div class="panel-container">
      <!-- Left Panel: User List -->
      <div class="left-panel">
        <div class="search-bar">
          <input type="text" id="searchInput" placeholder="Search users..." />
        </div>

        <div class="minib" id="userList">
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="box1" role="button" onclick="window.location.href='edit_role.php?user_id=<?= $row['id'] ?>'" style="cursor:pointer;">
              <div class="top-row">
                <p class="U"><?= htmlspecialchars($row['username']) ?></p>
                <div class="supervisor">
                  <p class="S"><?= htmlspecialchars($row['role_name']) ?></p>
                </div>
              </div>
              <p class="G"><?= htmlspecialchars($row['email']) ?></p>
              <p class="Eg"><?= htmlspecialchars($row['department_name']) ?></p>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <!-- Right Panel: Role Editing -->
      <div class="right-panel">
        <?php if ($selected_user): ?>
          <form method="POST" action="edit_role.php" class="role-form">
            <h3>Editing Role for: <?= htmlspecialchars($selected_user['username']) ?></h3>
            <input type="hidden" name="user_id" value="<?= $selected_user['id'] ?>">

            <div class="choices">
              <?php
              $roles = ['Admin', 'Supervisor', 'Manager', 'Employee'];
              foreach ($roles as $role) {
                  $active_class = ($role === $selected_role) ? 'selected-role' : '';
                  echo "<button type='button' class='Save $active_class' onclick=\"selectRole(this, '$role')\">$role</button>";
              }
              ?>
            </div>

            <input type="hidden" name="role" id="role_input" value="<?= htmlspecialchars($selected_role) ?>">

            <div class="button-container">
              <button type="button" class="cancel" onclick="window.location.href='role_assignment.php'">Return</button>
              <button type="submit" class="Save">Save</button>
            </div>
          </form>
        <?php else: ?>
          <div class="right-center">
            <p class="w">Select a user to assign a role.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<script>
function selectRole(button, role) {
  const buttons = document.querySelectorAll('.choices button');
  buttons.forEach(btn => btn.classList.remove('selected-role'));
  button.classList.add('selected-role');
  document.getElementById('role_input').value = role;
}

document.getElementById('searchInput').addEventListener('input', function () {
  const filter = this.value.toLowerCase();
  const users = document.querySelectorAll('.box1');
  users.forEach(user => {
    const username = user.querySelector('.U').textContent.toLowerCase();
    const role = user.querySelector('.S').textContent.toLowerCase();
    const email = user.querySelector('.G').textContent.toLowerCase();
    const department = user.querySelector('.Eg').textContent.toLowerCase();

    if (
      username.includes(filter) ||
      role.includes(filter) ||
      email.includes(filter) ||
      department.includes(filter)
    ) {
      user.style.display = '';
    } else {
      user.style.display = 'none';
    }
  });
});
</script>

<style>
.selected-role {
  background-color: #4CAF50 !important;
  color: white !important;
}
</style>
<style>
.right-panel {
  display: flex !important;
  justify-content: flex-end !important;
  align-items: center !important;
  height: 100% !important;
  position: relative !important;
  padding: 20px !important;
}

.w {
  font-weight: 600 !important;
  margin: 0 !important;
  max-width: 100% !important;
  text-align: right !important;
}
</style>
<style>
.right-panel {
  display: flex !important;
  justify-content: flex-end !important;
  align-items: center !important;
  height: 400px !important;
  position: relative !important;
  padding: 20px !important;
}

.w {
  font-weight: 600 !important;
  margin: 0 !important;
  max-width: 100% !important;
  text-align: right !important;
}
</style>
</body>
</html>

<?php $conn->close(); ?>