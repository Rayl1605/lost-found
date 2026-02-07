<?php
// Get connection info from Vercel Environment Variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Initialize MySQLi
$mysqli = mysqli_init();

// Required for Aiven's secure connection
$mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);

// Establish connection
$mysqli->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>