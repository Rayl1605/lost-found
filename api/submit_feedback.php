<?php
// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "lost_found_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Get and Sanitize Data
    $report_id = $_POST['report_id'];
    $finder_name = $conn->real_escape_string($_POST['finder_name']);
    $item_name = $conn->real_escape_string($_POST['item_name']);
    $message = $conn->real_escape_string($_POST['message']);

    // 4. Insert into Database
    $sql = "INSERT INTO feedback (report_id, finder_name, item_name, message) 
            VALUES ('$report_id', '$finder_name', '$item_name', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Thank you! Your feedback has been posted.');
                window.location.href = 'dashboard.php'; 
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>