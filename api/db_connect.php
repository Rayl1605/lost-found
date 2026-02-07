<?php
// Use getenv() to pull secrets from Vercel's environment variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD'); // No more hardcoded password here!
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>