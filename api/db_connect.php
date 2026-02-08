<?php
$mysqli = mysqli_init();
$mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);
$mysqli->real_connect(
    "mysql-2020e1cf-rayllyalmendras-0230.j.aivencloud.com", 
    "avnadmin", 
    "AVNS_9keDpfhiZkvFpPmSCzA", 
    "defaultdb", 
    19987, 
    NULL, 
    MYSQLI_CLIENT_SSL
);
if ($mysqli->connect_error) { die("Connection failed: " . $mysqli->connect_error); }
?>