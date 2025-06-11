<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="css/Details.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/supervisor_sidebar.php'; ?>
    <!-- Main Content -->
<main class="content">
    <?php include 'includes/supervisor_header.php'; ?> 

    <div class="modal">
    <div class="close-icon">×</div>
    <h2>Complete UI Design for Dashboard</h2>

    <div class="badges">
        <div class="choice">Website Redesign</div>
        <div class="Yellow">Submitted</div>
        <div class="Red">High</div>
    </div>

    <div class="tabs">
        <div class="tab"><a href="Details.php">Details</a></div>
        <div class="tab active"><a href="Attachment.php">Attachments(0)</a></div>
    </div>

    <div class="file">
        <img src="https://cdn.iconfinder.com/stored_data/2256390/128/png?token=1748100684-prWRN%2BwPKZWjtE7St8mszCa35UZESeEjDBKPDwG%2BtYE%3D" alt="">
        <input type="file">
    </div>

    <div class="buttons">
    <div class="left-buttons">
        <button class="btn btn-close">Close</button>
        <button class="btn btn-request">Request Changes</button>
    </div>
    <div class="right-buttons">
        <button class="btn btn-approve">Approved</button>
    </div>
    </div>


</div>
</main>
</body>
</html>
