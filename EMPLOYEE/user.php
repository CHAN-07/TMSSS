<?php
include '../config/connection.php';

// Query to fetch users excluding those with 'Admin' role, ordered by username ascending
$sql = "SELECT username, role FROM users WHERE role != 'Admin' ORDER BY username ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="css/task_review.css">
    <link rel="stylesheet" href="css/global/employee_sidebar.css" />
    <link rel="stylesheet" href="css/global/employee_header.css" />    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/employee_sidebar.php'; ?>

<main class="content">

<?php include 'includes/employee_header.php'; ?>
        
<!-- User List Section -->
        <div class="user-list scrollable-content">
            <div>
                <h2>Users</h2> 
             </div>  
           
            <div class="search-bar">
                <input id="userSearch" class="ins" type="text" placeholder="Search departments...">
            </div>
            <table border="0">
                <tr>
                    <th>Names</th>
                    <th>Role</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td data-label='Name'><h3>" . htmlspecialchars($row['username']) . "</h3></td>";
                        echo "<td data-label='Role'>" . htmlspecialchars($row['role']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No users found.</td></tr>";
                }
                ?>
            </table>
        </div>     

<script>
    // Client-side search/filter for user list table
    document.getElementById('userSearch').addEventListener('keyup', function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll('table tr:not(:first-child)');
        rows.forEach(function(row) {
            var username = row.querySelector('td[data-label="Name"] h3').textContent.toLowerCase();
            var role = row.querySelector('td[data-label="Role"]').textContent.toLowerCase();
            if (username.indexOf(filter) > -1 || role.indexOf(filter) > -1) {
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
