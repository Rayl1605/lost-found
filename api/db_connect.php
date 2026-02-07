<?php
// Pull credentials from Vercel environment variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = "3306"; 

// Initialize connection with a 10-second timeout
$conn = mysqli_init();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10); 

// Connect to the Aiven MySQL server
if (!$conn->real_connect($host, $user, $pass, $db, $port)) {
    error_log("Connection failed: " . mysqli_connect_error());
    die("The database is taking too long to respond. Please refresh the page.");
}

// Ensure the connection uses the correct character set for item descriptions
$conn->set_charset("utf8mb4");
?>