<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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

        <!-- Main Content -->
        <section class="user-list">
            <h2>User Management</h2>
            <div class="bastaword-wrapper">
                <h3 class="bastaword">Create and manage user accounts in your organization</h3>
            </div>

            <div class="search-bar" style="margin-bottom: 10px;">
                <input id="userSearch" class="ins" type="text" placeholder="Search users..." />
                <div class="adduser" role="button" onclick="window.location.href='add_user.php'">
                    <img src="https://cdn.iconfinder.com/stored_data/2278377/128/png?token=1749018254-ka0nEEMXx3iCj12o0AddNane4Bb3fEMMfBMW%2Bw8gXsE%3D" class="addb" />
                    <p class="useradd">Add user</p>
                </div>
            </div>

            <table border="0">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php
                include __DIR__ . '/../config/connection.php';

$department_filter = '';
$designation_filter = '';
if (isset($_GET['department_id']) && is_numeric($_GET['department_id'])) {
    $department_id = intval($_GET['department_id']);
    $department_filter = " WHERE u.department_id = $department_id ";
}
if (isset($_GET['designation']) && !empty($_GET['designation'])) {
    $designation = $conn->real_escape_string($_GET['designation']);
    if ($department_filter) {
        $designation_filter = " AND u.designation = '$designation' ";
    } else {
        $designation_filter = " WHERE u.designation = '$designation' ";
    }
}

$sql = "SELECT u.id, u.username, u.email, d.department_name, desig.designation_title AS designation, u.role, u.status 
        FROM users u 
        LEFT JOIN department d ON u.department_id = d.id
        LEFT JOIN designation desig ON u.designation = desig.designation_title
        $department_filter
        $designation_filter";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td data-label='Name'>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td data-label='Email'>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td data-label='Department'>" . htmlspecialchars($row['department_name']) . "</td>";
                        echo "<td data-label='Designation'>" . htmlspecialchars($row['designation']) . "</td>";
                        echo "<td data-label='Role'>" . htmlspecialchars($row['role']) . "</td>";
                        echo "<td data-label='Status'>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td data-label='Actions'>";
                        echo "<div class='action-buttons'>";
                        echo "<a href='edit_user.php?id=" . $row['id'] . "'>Edit</a> ";
                        echo "<a href='deactivate_user.php?id=" . $row['id'] . "' onclick=\"return confirm('Are you sure you want to deactivate this user?')\">Deactivate</a>";
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No users found.</td></tr>";
                }
                ?>
            </table>
        </section>
    </main>

<script>
document.getElementById('userSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tr:not(:first-child)');
    rows.forEach(row => {
        const name = row.querySelector('td[data-label="Name"]').textContent.toLowerCase();
        const email = row.querySelector('td[data-label="Email"]').textContent.toLowerCase();
        const role = row.querySelector('td[data-label="Role"]').textContent.toLowerCase();
        if (name.includes(filter) || email.includes(filter) || role.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
