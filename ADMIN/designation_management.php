<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Designation Management</title>
  <link rel="stylesheet" href="../CSS/department_mng.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
  <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>

<main class="content">
  <?php include __DIR__ . '/includess/admin_header.php'; ?>

  <section class="user-list">
    <h2>Designation Management</h2>
    <div class="bastaword-wrapper">
      <h3 class="bastaword">Create and manage designations in your organization</h3>
    </div>

    <div class="search-bar">
      <input id="designationSearch" class="ins" type="text" placeholder="Search designations...">
      <div class="adduser" role="button" onclick="window.location.href='add_designation.php'">
        <img src="https://cdn.iconfinder.com/stored_data/2278377/128/png?token=1749018254-ka0nEEMXx3iCj12o0AddNane4Bb3fEMMfBMW%2Bw8gXsE%3D" class="addb">
        <p class="useradd">Add designation</p>
      </div>
    </div>
  <table border="0">
    <tr>
      <th>Designation Title</th>
      <th>Department</th>
      <th>Employees</th>
      <th>Description</th>
      <th>Created At</th>
      <th>Actions</th>
    </tr>
    <?php
    include __DIR__ . '/../config/connection.php';

    $sql = "SELECT d.id, d.designation_title, dep.department_name, d.description, d.created_at,
                   (SELECT COUNT(*) FROM users u WHERE u.designation = d.designation_title) AS employee_count
            FROM designation d 
            LEFT JOIN department dep ON d.department = dep.id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td data-label='Designation Title'>" . htmlspecialchars($row['designation_title']) . "</td>";
            echo "<td data-label='Department'>" . htmlspecialchars($row['department_name']) . "</td>";
            echo "<td data-label='Employees'><a href='user_management.php?designation=" . urlencode($row['designation_title']) . "' class='employee-count' data-designation-id='" . $row['id'] . "'>" . htmlspecialchars($row['employee_count']) . "</a></td>";
            echo "<td data-label='Description'>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td data-label='Created At'>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td data-label='Actions'>";
            echo "<div class='action-buttons'>";
            echo "<a href='edit_designation.php?id=" . $row['id'] . "'>Edit</a> ";
            echo "<a href='delete_designation.php?id=" . $row['id'] . "' onclick=\"return confirm('Are you sure?')\">Delete</a>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No designations found.</td></tr>";
    }
    ?>
  </table>

  <script>
  document.getElementById('designationSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tr:not(:first-child)');
    rows.forEach(row => {
      const title = row.querySelector('td[data-label="Designation Title"]').textContent.toLowerCase();
      const department = row.querySelector('td[data-label="Department"]').textContent.toLowerCase();
      const description = row.querySelector('td[data-label="Description"]').textContent.toLowerCase();
      if (title.includes(filter) || department.includes(filter) || description.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
  </script>
</section>
</main>

</body>
</html>
