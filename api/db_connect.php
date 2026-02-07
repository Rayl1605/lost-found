<?php
$mysqli = mysqli_init();

// 1. Define the port OUTSIDE the function call
$port = getenv('DB_PORT') ?: 19987; 

// 2. Pass the variables into the function
$success = mysqli_real_connect(
    $mysqli,
    getenv('DB_HOST'),
    getenv('DB_USER'),
    getenv('DB_PASSWORD'),
    getenv('DB_NAME'),
    $port,             // Use the variable here
    null,
    MYSQLI_CLIENT_SSL
);

if (!$success) {
    die("Connect Error: " . mysqli_connect_error());
}
?>