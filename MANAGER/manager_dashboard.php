<?php
include '../config/connection.php';

$active_projects_count = 0;
$result = $conn->query("SELECT COUNT(*) AS count FROM projects WHERE status = 'Active'");
if ($result) {
    $row = $result->fetch_assoc();
    $active_projects_count = $row['count'] ?? 0;
}

$pending_tasks_count = 0;
$pending_result = $conn->query("SELECT COUNT(*) AS count FROM tasks WHERE status = 'Not Started'");
if ($pending_result) {
    $pending_row = $pending_result->fetch_assoc();
    $pending_tasks_count = $pending_row['count'] ?? 0;
}

$team_members_count = 0;
$team_members_query = "
    SELECT COUNT(*) AS count FROM (
        SELECT project_id FROM tasks GROUP BY project_id HAVING COUNT(DISTINCT assignee_id) > 1
    ) AS multi_assignee_projects
";
$team_members_result = $conn->query($team_members_query);
if ($team_members_result) {
    $team_members_row = $team_members_result->fetch_assoc();
    $team_members_count = $team_members_row['count'] ?? 0;
}

$upcoming_tasks = [];
$today = date('Y-m-d');
$three_days_later = date('Y-m-d', strtotime('+3 days'));
$upcoming_query = "SELECT title, due_date FROM tasks WHERE DATE(due_date) BETWEEN '$today' AND '$three_days_later' ORDER BY due_date ASC";
$upcoming_result = $conn->query($upcoming_query);
if ($upcoming_result && $upcoming_result->num_rows > 0) {
    while ($row = $upcoming_result->fetch_assoc()) {
        $upcoming_tasks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="css/manager_dashboard.css">
    <link rel="stylesheet" href="css/global/manager_sidebar.css">
    <link rel="stylesheet" href="css/global/manager_header.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/manager_sidebar.php'; ?>

<main class="content">

<?php include 'includes/manager_header.php'; ?>

    <h1 class="hello-admin">Hello, Manager</h1>

    <div class="dashboard-and-activity-wrapper">
        <section class="dashboard-cards">
            <div class="card">
                <h2>Active Projects</h2>
                <h3>Projects ahead of schedule</h3>
                <p><?php echo $active_projects_count; ?></p>
                <img src="https://cdn4.iconfinder.com/data/icons/48-bubbles/48/12.File-1024.png">
            </div>
            <div class="card">
                <h2>Pending Task</h2>
                <h3>Tasks require attention</h3>
                <p><?php echo $pending_tasks_count; ?></p>
                <img src="https://cdn4.iconfinder.com/data/icons/basic-ui-2-line/32/clock-time-ticker-times-hour-512.png">
            </div>
            <div class="card">
                <h2>Team Members</h2>
                <h3>Across all projects</h3>
                <p><?php echo $team_members_count; ?></p>
                <img src="https://cdn0.iconfinder.com/data/icons/users-android-l-lollipop-icon-pack/24/group2-512.png">
            </div>
        </section>

        <!-- Recent User Activity -->
        <div class="recent-activity-wrapper">
            <section class="recent-activity-panel">
                <div class="recent-activity-header">
                    <img src="https://cdn4.iconfinder.com/data/icons/essentials-71/24/011_-_Calendar-512.png" alt="User Icon" />
                    <h2>Upcoming deadline</h2>
                </div>
                <?php if (count($upcoming_tasks) > 0): ?>
                    <ul>
                        <?php foreach ($upcoming_tasks as $task): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($task['title']); ?></strong> - Due: <?php echo htmlspecialchars($task['due_date']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No upcoming deadlines</p>
                <?php endif; ?>
            </section>

            <section class="recent-activity-panel2">
                <h2>
                    <img src="https://cdn3.iconfinder.com/data/icons/social-media-2125/80/timeline-512.png">
                    Team Activity
                </h2>
                <form method="GET" id="activityFilterForm" style="margin-bottom: 10px;">
                    <label for="activityType">Filter by activity type:</label>
                    <select name="activityType" id="activityType" onchange="document.getElementById('activityFilterForm').submit();">
                        <option value="all" <?php if (!isset($_GET['activityType']) || $_GET['activityType'] === 'all') echo 'selected'; ?>>All</option>
                        <option value="started" <?php if (isset($_GET['activityType']) && $_GET['activityType'] === 'started') echo 'selected'; ?>>Started</option>
                        <option value="submitted" <?php if (isset($_GET['activityType']) && $_GET['activityType'] === 'submitted') echo 'selected'; ?>>Submitted</option>
                    </select>
                </form>
                <?php
                $team_activity = [];
                $activityType = $_GET['activityType'] ?? 'all';
                $statusFilter = '';
                if ($activityType === 'started') {
                    $statusFilter = "t.status = 'In Progress'";
                } elseif ($activityType === 'submitted') {
                    $statusFilter = "t.status = 'Submitted'";
                } else {
                    $statusFilter = "t.status IN ('Submitted', 'In Progress')";
                }
                $team_activity_query = "
                    SELECT u.username, t.title, p.name AS project_name, t.status, t.due_date
                    FROM tasks t
                    LEFT JOIN users u ON t.assignee_id = u.id
                    LEFT JOIN projects p ON t.project_id = p.id
                    WHERE $statusFilter
                    ORDER BY t.due_date DESC
                    LIMIT 5
                ";
                $team_activity_result = $conn->query($team_activity_query);
                if ($team_activity_result && $team_activity_result->num_rows > 0) {
                    while ($row = $team_activity_result->fetch_assoc()) {
                        $team_activity[] = $row;
                    }
                }
                ?>
                <?php
                function time_elapsed_string($datetime, $full = false) {
                    $now = new DateTime;
                    $ago = new DateTime($datetime);
                    $diff = $now->diff($ago);

                    $diff->w = floor($diff->d / 7);
                    $diff->d -= $diff->w * 7;

                    $string = array(
                        'y' => 'year',
                        'm' => 'month',
                        'w' => 'week',
                        'd' => 'day',
                        'h' => 'hour',
                        'i' => 'minute',
                        's' => 'second',
                    );
                    foreach ($string as $k => &$v) {
                        if ($diff->$k) {
                            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                        } else {
                            unset($string[$k]);
                        }
                    }

                    if (!$full) $string = array_slice($string, 0, 1);
                    return $string ? implode(', ', $string) . ' ago' : 'just now';
                }
                ?>
                <?php if (count($team_activity) > 0): ?>
                    <ul style="list-style:none; padding-left:0;">
                        <?php foreach ($team_activity as $activity): ?>
                            <li style="margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                                <?php
                                $status = $activity['status'];
                                $username = htmlspecialchars($activity['username']);
                                $title = htmlspecialchars($activity['title']);
                                $project = htmlspecialchars($activity['project_name']);
                                $due_date = $activity['due_date'] ?? null;
                                $time_ago = $due_date ? time_elapsed_string($due_date) : '';

                                $status_text = '';
                                $status_color = '';
                                if ($status === 'Submitted') {
                                    $status_text = 'submitted';
                                    $status_color = 'orange';
                                } elseif ($status === 'In Progress') {
                                    $status_text = 'started';
                                    $status_color = 'blue';
                                } elseif ($status === 'Completed') {
                                    $status_text = 'completed';
                                    $status_color = 'green';
                                }

                                echo "<strong>$username</strong> <span style='color: $status_color;'>$status_text</span> the task <strong>$title</strong>";
                                echo " <br><small style='color: #666;'>$time_ago</small>";
                                ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent user activity</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

</body>
</html>
