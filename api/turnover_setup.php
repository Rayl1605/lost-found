<?php
require_once 'db_connect.php';

// SECURITY CONFIGURATION 
// This is the password to the Campus Administrator.
define('SETUP_KEY', 'ADMIN123'); 

$msg = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_key = $_POST['setup_key'];
    
    // 1. Verify the Setup Key
    if ($entered_key !== SETUP_KEY) {
        $msg = "Invalid Setup Key! Access Denied.";
        $msg_type = "error";
    } else {
        // 2. Proceed with Account Creation
        $name = $conn->real_escape_string($_POST['name']);
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $msg = "Passwords do not match.";
            $msg_type = "error";
        } else {
            // Check existence
            $check = $conn->query("SELECT admin_id FROM admin WHERE username='$username' OR email='$email'");
            if ($check->num_rows > 0) {
                $msg = "Username or Email already exists.";
                $msg_type = "error";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO admin (name, username, email, gmail, phone_number, password) 
                        VALUES ('$name', '$username', '$email', '$email', '$phone', '$hashed_password')";

                if ($conn->query($sql) === TRUE) {
                    $msg = "<b>Success!</b> Admin '$username' created. <br> <span class='text-red-500 font-bold'>PLEASE DELETE THIS FILE NOW.</span> <a href='admin_login.php' class='underline'>Login Here</a>";
                    $msg_type = "success";
                } else {
                    $msg = "Database Error: " . $conn->error;
                    $msg_type = "error";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Turnover - Setup Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap'); body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-8 border-4 border-yellow-400 relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full bg-yellow-400 text-slate-900 text-center text-xs font-bold py-1 uppercase tracking-wider">
            Turnover Mode - Emergency Access
        </div>

        <div class="text-center mb-8 mt-4">
            <i class="fas fa-tools text-5xl text-slate-800 mb-4"></i>
            <h1 class="text-2xl font-bold text-slate-800">System Setup</h1>
            <p class="text-slate-500 text-sm">Create the first Admin Account.</p>
        </div>

        <?php if($msg): ?>
            <div class="p-4 rounded-xl mb-6 text-sm font-medium border <?php echo $msg_type == 'success' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            
            <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-200 mb-4">
                <label class="block text-xs font-bold text-yellow-700 uppercase mb-2">System Setup Key</label>
                <input type="password" name="setup_key" class="w-full p-3 bg-white border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="Enter the Master Key" required>
                <p class="text-[10px] text-yellow-600 mt-1">Ask the developer for this key.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="name" class="w-full p-3 bg-gray-50 border rounded-xl text-sm" placeholder="Full Name" required>
                <input type="text" name="phone" class="w-full p-3 bg-gray-50 border rounded-xl text-sm" placeholder="Phone Number" required>
            </div>

            <input type="text" name="username" class="w-full p-3 bg-gray-50 border rounded-xl text-sm" placeholder="Desired Username" required>
            <input type="email" name="email" class="w-full p-3 bg-gray-50 border rounded-xl text-sm" placeholder="Admin Email" required>

            <div class="grid grid-cols-2 gap-4">
                <input type="password" name="password" class="w-full p-3 bg-gray-50 border rounded-xl text-sm" placeholder="Password" required>
                <input type="password" name="confirm_password" class="w-full p-3 bg-gray-50 border rounded-xl text-sm" placeholder="Confirm" required>
            </div>

            <button type="submit" class="w-full bg-slate-800 text-white font-bold py-3 rounded-xl hover:bg-slate-700 transition shadow-lg mt-4">
                Create Root Admin
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-xs text-red-500 font-bold">IMPORTANT:</p>
            <p class="text-xs text-gray-400">Delete this file (`turnover_setup.php`) immediately after creating the admin account to secure the system.</p>
        </div>

    </div>
</body>
</html>