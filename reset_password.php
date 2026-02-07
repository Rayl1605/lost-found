<?php
require_once 'db_connect.php';

$message = "";
$token = $_GET['token'] ?? "";

if (!$token) {
    die("Invalid request.");
}

$token_hash = hash("sha256", $token);

// Validate Token
$sql = "SELECT * FROM users WHERE reset_token_hash = '$token_hash' AND reset_token_expires_at > NOW()";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    die("Link is invalid or has expired.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update DB and Clear Token
        $sql = "UPDATE users SET password = '$hashed_password', reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = {$user['id']}";
        
        if ($conn->query($sql)) {
            header("Location: login.php?msg=Password reset successful! Please login.");
            exit();
        } else {
            $message = "Error updating password.";
        }
    } else {
        $message = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Poppins', sans-serif; }</style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Set New Password</h2>

        <?php if($message): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">New Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl">Reset Password</button>
        </form>
    </div>
</body>
</html>