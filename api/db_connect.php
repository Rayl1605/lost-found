<?php
$host = 'YOUR_NEW_HOST_FROM_AIVEN'; // Update this!
$user = 'avnadmin';
$pass = 'YOUR_PASSWORD';
$db   = 'defaultdb';
$port = 19987;

$mysqli = mysqli_init();
// Aiven requires SSL. Ensure you have the ca.pem file if needed.
if (!$mysqli->real_connect($host, $user, $pass, $db, $port)) {
    die("Connect Error: " . mysqli_connect_error());
}
?>