<?php
session_start();
require_once 'db_connect.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            header("Location: " . ($row['role'] === 'admin' ? "admin_dashboard.php" : "dashboard.php"));
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In - MCTI Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .float-img { animation: float 6s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .custom-input {
            background-color: #f3f4f6;
            border: 1px solid transparent;
            transition: all 0.3s;
        }
        .custom-input:focus-within {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4 lg:p-8">

    <div class="max-w-6xl w-full flex flex-col-reverse lg:flex-row items-center justify-center gap-16">

        <div class="w-full lg:w-[450px] bg-white rounded-[20px] shadow-2xl overflow-hidden z-20">
            
            <div class="flex bg-gray-50 border-b border-gray-100">
                <div class="flex-1 py-4 text-center border-b-2 border-blue-600 bg-white cursor-default">
                    <span class="text-blue-600 font-bold text-sm">Sign In</span>
                </div>
                <a href="register.php" class="flex-1 py-4 text-center border-b-2 border-transparent text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                    <span class="font-bold text-sm">Sign Up</span>
                </a>
                <a href="forgot_password.php" class="flex-1 py-4 text-center border-b-2 border-transparent text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                    <span class="font-bold text-sm">Recovery</span>
                </a>
            </div>

            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 mb-6 opacity-80">
                         <div class="w-6 h-6 bg-blue-600 rounded flex items-center justify-center text-white text-xs">
                            <i class="fas fa-search"></i>
                         </div>
                         <span class="text-sm font-bold text-gray-600 tracking-wide">MCTI SYSTEM</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Sign In</h2>
                    <div class="w-10 h-1 bg-blue-600 mx-auto mt-2 rounded-full"></div>
                </div>

                <?php if(isset($_GET['msg'])): ?>
                    <div class="mb-5 bg-green-50 text-green-600 px-3 py-2 rounded-lg text-xs font-bold flex items-center gap-2 border border-green-100">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="mb-5 bg-red-50 text-red-600 px-3 py-2 rounded-lg text-xs font-bold flex items-center gap-2 border border-red-100">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="space-y-6">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Login / Email</label>
                        <div class="custom-input rounded-xl px-4 py-3 flex items-center gap-3">
                            <i class="fas fa-user text-gray-400"></i>
                            <input type="text" name="username" class="bg-transparent w-full outline-none text-sm font-medium text-gray-700 placeholder-gray-400" placeholder="Enter username" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Password</label>
                        <div class="custom-input rounded-xl px-4 py-3 flex items-center gap-3">
                            <i class="fas fa-lock text-gray-400"></i>
                            <input type="password" name="password" class="bg-transparent w-full outline-none text-sm font-medium text-gray-700 placeholder-gray-400" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-500 text-sm"></i>
                        <span class="text-xs text-gray-500 font-medium">I agree to MCTI <a href="terms.php" target="_blank" class="text-blue-600 underline hover:text-blue-800 transition">Terms of use</a></span>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold text-sm shadow-lg shadow-blue-500/30 hover:shadow-xl hover:scale-[1.02] transition-all">
                        Sign In
                    </button>
                    
                    <div class="mt-4 text-center border-t border-gray-100 pt-4">
                        <a href="admin_login.php" class="text-xs text-gray-400 hover:text-blue-600 font-semibold transition flex items-center justify-center gap-2">
                            <i class="fas fa-user-shield"></i> Admin Login
                        </a>
                    </div>
                    
                </form>
            </div>
        </div>

        <div class="hidden lg:flex flex-col items-center justify-center text-center relative z-10 max-w-lg">
            
            <div class="mb-8">
                <h1 class="text-4xl font-extrabold text-slate-800 leading-tight">
                    Welcome to the <br> <span class="text-blue-600">MCTI Digital Portal</span>
                </h1>
                <p class="text-slate-500 mt-4 text-lg">Connect, report, and recover your lost items instantly.</p>
            </div>

            <img src="login-image.png" 
                 alt="3D Illustration" 
                 class="w-full max-w-md float-img drop-shadow-2xl">
                 
        </div>

    </div>
</body>
</html>