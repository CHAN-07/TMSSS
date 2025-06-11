<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Department Management</title>
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
    <h2>Department Management</h2>
    <div class="bastaword-wrapper">
      <h3 class="bastaword">Create and manage departments in your organization</h3>
    </div>

    <div class="search-bar">
      <input id="departmentSearch" class="ins" type="text" placeholder="Search departments...">
      <div class="adduser" role="button" onclick="window.location.href='add_department.php'">
        <img src="https://cdn.iconfinder.com/stored_data/2278377/128/png?token=1749018254-ka0nEEMXx3iCj12o0AddNane4Bb3fEMMfBMW%2Bw8gXsE%3D" class="addb">
        <p class="useradd">Add department</p>
      </div>
    </div>

    <table border="0">
      <tr>
        <th>Department Name</th>
        <th>Description</th>
        <th>Employees</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
<?php
// AJAX handler to fetch employees by department_id
if (isset($_GET['fetch_employees']) && isset($_GET['department_id'])) {
    include __DIR__ . '/../config/connection.php';
    $department_id = intval($_GET['department_id']);
    $stmt = $conn->prepare("SELECT username, email, role, status FROM users WHERE department_id = ?");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($employees);
    exit;
}

      include __DIR__ . '/../config/connection.php';

      $sql = "SELECT d.id, d.department_name, d.description, COUNT(u.id) AS employee_count, d.created_at
              FROM department d
              LEFT JOIN users u ON d.id = u.department_id
              GROUP BY d.id, d.department_name, d.description, d.created_at";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td data-label='Department Name'>" . htmlspecialchars($row['department_name']) . "</td>";
              echo "<td data-label='Description'>" . htmlspecialchars($row['description']) . "</td>";
              echo "<td data-label='Employees'><a href='user_management.php?department_id=" . $row['id'] . "'>" . htmlspecialchars($row['employee_count']) . "</a></td>";
              echo "<td data-label='Created At'>" . htmlspecialchars($row['created_at']) . "</td>";
              echo "<td data-label='Actions'>";
              echo "<div class='action-buttons'>";
              echo "<a href='edit_dept.php?id=" . $row['id'] . "'>Edit</a> ";
              echo "<a href='delete_department.php?id=" . $row['id'] . "' onclick=\"return confirm('Are you sure?')\">Delete</a>";
              echo "</div>";
              echo "</td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='5'>No departments found.</td></tr>";
      }
      ?>
    </table>
  </section>

  <!-- Modal for displaying employees -->
  <div id="employeeModal" class="modal" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px; position: relative;">
      <span id="closeModal" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
      <h3>Employees in Department</h3>
      <div class="search-bar" style="margin-bottom: 10px;">
        <input id="employeeSearch" class="ins" type="text" placeholder="Search employees..." />
      </div>
      <table id="employeeTable" border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <!-- Employee rows will be inserted here -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
  document.getElementById('departmentSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tr:not(:first-child)');
    rows.forEach(row => {
      const name = row.querySelector('td[data-label="Department Name"]').textContent.toLowerCase();
      const description = row.querySelector('td[data-label="Description"]').textContent.toLowerCase();
      const employees = row.querySelector('td[data-label="Employees"]').textContent.toLowerCase();
      if (name.includes(filter) || description.includes(filter) || employees.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  // Modal handling
  const modal = document.getElementById('employeeModal');
  const closeModal = document.getElementById('closeModal');
  const employeeTableBody = document.querySelector('#employeeTable tbody');

  closeModal.onclick = function() {
    modal.style.display = 'none';
    employeeTableBody.innerHTML = '';
  };

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = 'none';
      employeeTableBody.innerHTML = '';
    }
  };

  // Fetch and display employees on click
  document.querySelectorAll('.employee-count').forEach(element => {
    element.addEventListener('click', function(event) {
      event.preventDefault();
      const departmentId = this.getAttribute('data-department-id');
      fetch(`?fetch_employees=1&department_id=${departmentId}`)
        .then(response => response.json())
        .then(data => {
          employeeTableBody.innerHTML = '';
          if (data.length === 0) {
            employeeTableBody.innerHTML = '<tr><td colspan="4">No employees found.</td></tr>';
          } else {
            data.forEach(emp => {
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${emp.username}</td>
                <td>${emp.email}</td>
                <td>${emp.role}</td>
                <td>${emp.status}</td>
              `;
              employeeTableBody.appendChild(row);
            });
          }
          modal.style.display = 'block';
        })
        .catch(error => {
          employeeTableBody.innerHTML = '<tr><td colspan="4">Error loading employees.</td></tr>';
          modal.style.display = 'block';
        });
    });
  });
  </script>

  <script>
  // Employee search filter
  document.getElementById('employeeSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#employeeTable tbody tr');
    rows.forEach(row => {
      const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
      const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
      const role = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
      if (name.includes(filter) || email.includes(filter) || role.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
  </script>
</main>

</body>
</html>
