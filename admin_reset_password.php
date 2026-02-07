<?php
// admin_reset_password.php
require_once 'db_connect.php';

$token = $_GET['token'] ?? '';
$message = '';
$valid_token = false;

if (!$token) {
    die("<div style='padding:20px; text-align:center; color:red;'>Invalid request. No token provided.</div>");
}

$token_hash = hash("sha256", $token);

// 1. Verify if Token is valid and not expired
$sql = "SELECT * FROM admin WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $valid_token = true;
} else {
    die("<div style='padding:20px; text-align:center; color:red;'>This password reset link is invalid or has expired.</div>");
}

// 2. Handle Password Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Password must be at least 6 characters.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Passwords do not match.</div>";
    } else {
        // HASHING:
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE admin SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE admin_id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_hash, $user['admin_id']);
        
        if ($stmt->execute()) {
            echo "<script>
                alert('Password successfully updated! You can now login.');
                window.location.href='admin_login.php';
            </script>";
            exit;
        } else {
            $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Database error. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Set New Password</h2>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block mb-2 text-sm font-bold text-gray-700">New Password</label>
                <input type="password" name="password" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" placeholder="******">
            </div>
            
            <div class="mb-6">
                <label class="block mb-2 text-sm font-bold text-gray-700">Confirm Password</label>
                <input type="password" name="confirm_password" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" placeholder="******">
            </div>
            
            <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition">Update Password</button>
        </form>
    </div>
</body>
</html>