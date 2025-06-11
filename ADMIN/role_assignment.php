<?php
require_once __DIR__ . '/../config/connection.php';

// Query to get all users with their roles and departments
$sql = "SELECT users.id, users.username, users.email, users.role, 
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
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../CSS/role_ass.css">
  <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
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
    <div class="left-panel">
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search users..." />
      </div>

      <div class="minib" id="userList">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="box1" role="button" onclick="window.location.href='edit_role.php?user_id=<?= $row['id'] ?>'">
              <div class="top-row">
                <p class="U"><?= htmlspecialchars($row['username']) ?></p>
                <div class="supervisor">
                  <p class="S"><?= htmlspecialchars($row['role']) ?></p>
                </div>
              </div>
              <p class="G"><?= htmlspecialchars($row['email']) ?></p>
              <p class="Eg"><?= htmlspecialchars($row['department_name']) ?></p>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No users found.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="right-panel">
      <button class="add" type="button" onclick="window.location.href='edit_role.php'">
        <img src="https://cdn0.iconfinder.com/data/icons/users-android-l-lollipop-icon-pack/24/add_user-1024.png" alt="Add Icon" />
      </button>
      <p class="w">Select User to assigned roles and permission</p>
    </div>
  </div>
</div>

</main>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
  const filter = this.value.toLowerCase();
  const userList = document.getElementById('userList');
  const users = userList.getElementsByClassName('box1');

  Array.from(users).forEach(function(user) {
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
</body>
</html>
