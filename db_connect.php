<?php
// Retrieve credentials from Vercel Environment Variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: '3306';

// Establish connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("A database error occurred. Please try again later.");
}

// Set charset to avoid encoding issues with item descriptions
$conn->set_charset("utf8mb4");
?>