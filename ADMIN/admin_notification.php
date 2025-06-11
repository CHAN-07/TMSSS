<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <link rel="stylesheet" href="admin_notification.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ . '/../ADMIN/includess/admin_sidebar.php'; ?>

<main class="content">

  <?php include __DIR__ . '/includess/admin_header.php'; ?>

        <div class="box">
            <!-- Title or header for the notification box -->
            <div class="word">Notifications</div>

            <!-- Scrollable container for notification items -->
            <div class="minib">
                <!-- Single notification item -->
                <div class="box1">
                <div class="user-role-row">
                    <!-- User or role name -->
                    <span class="U">John Doe</span>
                    <!-- Optional role tag with icon -->
                    <div class="supervisor">
                    <p class="S">Supervisor</p>
                    </div>
                </div>
                <!-- Notification message -->
                <p class="G">You have 3 new tasks assigned.</p>
                <!-- Timestamp or extra info -->
                <p class="Eg">5 minutes ago</p>
                </div>

                <div class="box1">
                <div class="user-role-row">
                    <span class="U">Jane Smith</span>
                    <div class="supervisor">
                    <p class="S">Supervisor</p>
                    </div>
                </div>
                <p class="G">Meeting scheduled at 2 PM.</p>
                <p class="Eg">1 hour ago</p>
                </div>

                <!-- Add more notification items as needed -->
            </div>

            <!-- Buttons or links at the bottom -->
            <div class="mark-read">
                <button>Mark all as read</button>
            </div>

            <div class="view-all">
                <a href="#">View all notifications</a>
            </div>
    </main>
    </body>
    </html