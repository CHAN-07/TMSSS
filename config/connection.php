<?php
$host = 'localhost';
$user = 'frm';
$password = 'Frenchie23*';
$database = 'tms';

// Create the connection using procedural style
$conn = mysqli_connect($host, $user, $password, $database);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to UTF-8
mysqli_set_charset($conn, "utf8");
?>
