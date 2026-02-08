<?php
$host = 'mysql-2020e1cf-rayllylalmendras-0230.j.aivencloud.com'; // Update this!
$user = 'avnadmin';
$pass = 'AVNS_9keDpfhiZkvFpPmSCzA';
$db   = 'defaultdb';
$port = 19987;

$mysqli = mysqli_init();
// Aiven requires SSL. Ensure you have the ca.pem file if needed.
if (!$mysqli->real_connect($host, $user, $pass, $db, $port)) {
    die("Connect Error: " . mysqli_connect_error());
}
?>