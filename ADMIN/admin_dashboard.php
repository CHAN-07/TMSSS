<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . '/../config/connection.php';

// Query to get total users count
$userCount = 0;
$sql = "SELECT COUNT(*) AS total FROM users";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $userCount = $row['total'];
}

// Query to get total departments count
$departmentCount = 0;
$sqlDept = "SELECT COUNT(*) AS total FROM department";
$resultDept = $conn->query($sqlDept);
if ($resultDept) {
    $rowDept = $resultDept->fetch_assoc();
    $departmentCount = $rowDept['total'];
}

// Query to get total designations count
$designationCount = 0;
$sqlDesig = "SELECT COUNT(*) AS total FROM designation";
$resultDesig = $conn->query($sqlDesig);
if ($resultDesig) {
    $rowDesig = $resultDesig->fetch_assoc();
    $designationCount = $rowDesig['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../css/admin.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>

<main class="content">

  <?php include __DIR__ . '../includess/admin_header.php'; ?>

    <section class="dashboard-cards">
    <div class="card">
      <h3>Total Users</h3>
      <p><?php echo $userCount; ?></p>
      <img src="https://cdn0.iconfinder.com/data/icons/users-android-l-lollipop-icon-pack/24/group2-512.png">
    </div>
    <div class="card">
      <h3>Departments</h3>
      <p><?php echo $departmentCount; ?></p>
      <img src="https://cdn4.iconfinder.com/data/icons/48-bubbles/48/03.Office-1024.png">
    </div>
    <div class="card">
      <h3>Designations</h3>
      <p><?php echo $designationCount; ?></p>
      <img src="https://cdn0.iconfinder.com/data/icons/phosphor-light-vol-4/256/suitcase-light-512.png">
    </div>
  </section>  

  <section class="recent-activity-panel">
    <h2>Recent User Activity</h2>

    <form method="GET" action="admin_dashboard.php" class="filter-form" style="margin-bottom: 20px;">
      <label for="filter_date">Filter by Date:</label>
      <input type="date" id="filter_date" name="filter_date" value="<?php echo isset($_GET['filter_date']) ? htmlspecialchars($_GET['filter_date']) : ''; ?>" />
      <button type="submit">Filter Activities</button>
      <button type="button" onclick="window.location.href='admin_dashboard.php'">Reset</button>
    </form>

    <?php
    $filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');

$stmt = $conn->prepare("SELECT username, role, login_time FROM user_login_activity WHERE DATE(login_time) = ? ORDER BY login_time DESC");
    $stmt->bind_param("s", $filter_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $formatted_date = date('M d, Y H:i', strtotime($row['login_time']));
            echo '<div class="activity-row">';
            echo '  <div class="activity-info">';
            echo '    <div class="user-name">' . htmlspecialchars($row['username']) . ' (' . htmlspecialchars($row['role']) . ')</div>';
            echo '    <div class="activity-date">' . $formatted_date . '</div>';
            echo '    <div class="activity-subtitle">Recent User Activity</div>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo '<p>No activities found for the selected date.</p>';
    }

    $stmt->close();
    ?>
  </section>
  
  
</main>

</body>
</html>
