<?php
session_start();
require_once 'db_connect.php';
require_once 'mail_config.php';

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // 1. Generate a secure token
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token); 
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // Expires in 30 mins

        // 2. Update User Record
        $update = "UPDATE users SET reset_token_hash = '$token_hash', reset_token_expires_at = '$expiry' WHERE email = '$email'";
        if ($conn->query($update)) {
            // 3. Send Email
            if (sendPasswordResetEmail($email, $token)) {
                $message = "Reset link sent! Please check your email inbox.";
                $msg_type = "success";
            } else {
                $message = "Error sending email. Please try again.";
                $msg_type = "error";
            }
        }
    } else {
        $message = "If that email exists, we have sent a reset link.";
        $msg_type = "info";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 text-2xl">
                <i class="fas fa-key"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Forgot Password?</h2>
            <p class="text-gray-500 text-sm mt-2">No worries! Enter your email and we'll send you reset instructions.</p>
        </div>

        <?php if($message): ?>
            <div class="p-3 rounded-lg text-sm mb-6 <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your email">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg">
                Send Reset Link
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="login.php" class="text-sm text-gray-500 hover:text-gray-800 flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>