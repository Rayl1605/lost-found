<?php
session_start();
require_once 'db_connect.php';

// Enable error reporting to debug "white screen" issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // 1. Fetch user by username
    $sql = "SELECT * FROM admin WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 2. Verify the Hashed Password
        // This checks if the input matches the $2y$10$... hash in your database
        if (password_verify($password, $row['password'])) {
            
            // 3. Set Session Variables
            // We use 'admin_id' because that is your primary key column
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // e.g., 'Super Admin'

            // 4. Redirect to Dashboard
            // IMPORTANT: Make sure your dashboard file is actually named 'admin_dashboard.php'
            header("Location: admin_dashboard.php");
            exit();

        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin account not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MCT Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row w-full max-w-4xl overflow-hidden m-4">
        
        <div class="w-full md:w-1/2 bg-blue-600 p-12 text-white flex flex-col justify-center items-center text-center relative">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-800 opacity-50"></div>
            <div class="relative z-10">
                <div class="bg-white/20 p-6 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6 backdrop-blur-sm">
                    <i class="fas fa-search-location text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold mb-2">Lost & Found</h1>
                <p class="text-blue-100 mb-8 font-light">Marvelous College of Technology, Inc.</p>
                <p class="text-sm text-blue-100/80 italic">"Recovering items, reconnecting people."</p>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-12 flex flex-col justify-center">
            <div class="text-left mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back!</h2>
                <p class="text-gray-500">Please sign in to continue.</p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-gray-700 text-xs font-bold mb-2 uppercase tracking-wide">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="username" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:bg-white focus:border-blue-500 transition-colors" placeholder="Enter username" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-xs font-bold mb-2 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:bg-white focus:border-blue-500 transition-colors" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-8">
                    <label class="flex items-center text-sm text-gray-500 hover:text-gray-700 cursor-pointer">
                        <input type="checkbox" class="mr-2 leading-tight">
                        <span>Remember me</span>
                    </label>
                    <a href="admin_forgot_password.php" class="text-sm text-blue-600 hover:text-blue-800 font-semibold hover:underline">Forgot Password?</a>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-200 transform hover:-translate-y-0.5">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">Not an Admin? <a href="login.php" class="text-blue-600 font-bold hover:underline">User Login</a></p>
            </div>
        </div>
    </div>

</body>
</html>