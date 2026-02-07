<?php
// admin_forgot_password.php
require_once 'db_connect.php';

// 1. Load Email Configuration
$mail_ready = false;
if (file_exists('mail_config.php')) {
    require_once 'mail_config.php';
    if (isset($mail)) {
        $mail_ready = true;
    }
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // 2. Check if email exists in ADMIN table
    $sql = "SELECT * FROM admin WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        
        if ($mail_ready) {
            // 3. Generate Token
            $token = bin2hex(random_bytes(16));
            $token_hash = hash("sha256", $token);
            $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 mins

            // 4. Save Token
            $update_sql = "UPDATE admin SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sss", $token_hash, $expiry, $email);
            $stmt->execute();

            // 5. Send Email
            try {
                $mail->clearAddresses();
                $mail->addAddress($email);

                $resetLink = "http://localhost/lost-found/admin_reset_password.php?token=" . $token;

                $mail->isHTML(true);
                $mail->Subject = 'Admin Password Reset';
                $mail->Body    = "
                    <h3>Password Reset Request</h3>
                    <p>Click the link below to reset your password:</p>
                    <p><a href='$resetLink'>$resetLink</a></p>
                ";

                $mail->send();
                $message = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>✅ Reset link sent! Check your email.</div>";
            } catch (Exception $e) {
                $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>❌ Mailer Error: " . $mail->ErrorInfo . "</div>";
            }
        } else {
            $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>❌ Error: 'mail_config.php' is not configured correctly. The \$mail variable is missing.</div>";
        }

    } else {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>❌ No admin account found with that email.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Admin Recovery</h2>
            <p class="text-sm text-gray-500">Enter your email to reset password</p>
        </div>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <label class="block mb-2 text-sm font-bold text-gray-700">Email Address</label>
            <input type="email" name="email" required class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="admin@example.com">
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition">Send Reset Link</button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="admin_login.php" class="text-sm text-gray-500 hover:text-blue-600">Back to Login</a>
        </div>
    </div>
</body>
</html>