<?php
session_start();
// Include database connection
include 'config/connection.php';

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['user'] ?? '');
    $password = trim($_POST['pass'] ?? '');

    if ($username === '' || $password === '') {
        $login_error = 'Please enter both username and password.';
    } else {
        // Prepare and execute query to check user credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Password stored as plain text, compare directly
if ($password === $user['password']) {
    // Check if user is active
    if ($user['status'] !== 'active') {
        $login_error = 'Your account is deactivated. Please contact admin.';
    } else {
        // Login successful
        // Store user info and role in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // assuming 'role' column exists

        // Insert login activity record
        $stmtLog = $conn->prepare("INSERT INTO user_login_activity (user_id, username, role) VALUES (?, ?, ?)");
        $stmtLog->bind_param("iss", $user['id'], $user['username'], $user['role']);
        $stmtLog->execute();
        $stmtLog->close();

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ADMIN/admin_dashboard.php");
        } elseif ($user['role'] === 'manager') {
            header("Location: MANAGER/manager_dashboard.php");
        } elseif ($user['role'] === 'supervisor') {
            header("Location: SUPERVISOR/supervisor_dashboard.php");
        } elseif ($user['role'] === 'employee') {
            header("Location: EMPLOYEE/employee_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
} else {
    $login_error = 'Invalid username or password.';
}
        } else {
            $login_error = 'Invalid username or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
    <div class="container">
        <h1 id="prodify">Prodify.</h1>
        <div id="form">
            <center><h1>Login</h1></center>
            <?php if ($login_error): ?>
                <p style="color: red; font-weight: bold; text-align: center;"><?= htmlspecialchars($login_error) ?></p>
            <?php endif; ?>
            <form name="form" action="" method="POST" onsubmit="return true;">
                <label>Username: </label>
                <input type="text" id="user" name="user" required value="<?= htmlspecialchars($_POST['user'] ?? '') ?>" /><br /><br />

                <label>Password:</label>
                <input type="password" id="pass" name="pass" required /><br /><br />

                <input type="submit" id="btn" value="Login" name="submit" /><br />
            </form>
        </div>
    </div>
</body>
</html>
