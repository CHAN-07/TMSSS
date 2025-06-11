<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message</title>
    <link rel="stylesheet" href="../css/messages.css">
    <link rel="stylesheet" href="../CSS/global.css/admin_sidebar.css" />
    <link rel="stylesheet" href="../CSS/global.css/admin_header.css" />
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ . '/includess/admin_sidebar.php'; ?>
    <!-- Main Content -->
<main class="content">
    
<?php include __DIR__ . '/includess/admin_header.php'; ?>

    <div class="chatbox-container">
            <div class="chatbox">
                <div id="user-list">
                    <input type="text" class="search" placeholder="🔍 Search..." aria-label="Search users" /> 
                    <div class="chat-user active" tabindex="0"><img class="chatimg" src="../images/8665306_circle_user_icon.png">John</div>
                    <div class="chat-user" tabindex="0"><img class="chatimg" src="../images/8665306_circle_user_icon.png">Jane</div>
                    <div class="chat-user" tabindex="0"><img class="chatimg" src="../images/8665306_circle_user_icon.png">Mike</div>
                    <!-- Add more users dynamically here -->
                </div>

                <div class="chat-area">
                    <div id="chat-header" aria-live="polite" aria-atomic="true"><img class="chatimg" src="../images/8665306_circle_user_icon.png">John</div>

                    <div id="chat-messages" role="log" aria-live="polite" aria-relevant="additions">
                        <div class="message left" tabindex="0">Hello!</div>
                        <div class="message right" tabindex="0">Hi there! How can I help?</div>
                        <!-- Messages from DB will go here -->
                    </div>

                    <form id="chat-form" method="post" enctype="multipart/form-data" aria-label="Send message form">
                        <label for="file-input" class="attach-label" title="Attach file">📎</label>
                        <input type="file" id="file-input" name="attachment" aria-label="Attach file" />
                        <input type="text" name="message" placeholder="Type a message..." required aria-required="true" aria-label="Message input" />
                        <button type="submit" class="send" aria-label="Send message">Send</button>
                    </form>
                </div>
            </div>
        </div>
</html>