<?php
session_start();

// Check if the user is logged in as an Admin.
// If 'admin_id' is missing from the session, it means they are NOT an admin.
if (!isset($_SESSION['admin_id'])) {

    header("Location: admin_login.php");
    exit();
}

require_once 'db_connect.php';

$msg = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        $msg = "Passwords do not match.";
        $msg_type = "error";
    } else {
        // 1. Check if username/email exists in ADMIN table
        $check = $conn->query("SELECT admin_id FROM admin WHERE username='$username' OR email='$email'");
        if ($check->num_rows > 0) {
            $msg = "Username or Email already exists.";
            $msg_type = "error";
        } else {
            // 2. Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 3. Insert into ADMIN table
            $sql = "INSERT INTO admin (name, username, email, gmail, phone_number, password) 
                    VALUES ('$name', '$username', '$email', '$email', '$phone', '$hashed_password')";

            if ($conn->query($sql) === TRUE) {
                $msg = "Success! New Admin <b>$username</b> created.";
                $msg_type = "success";
            } else {
                $msg = "Database Error: " . $conn->error;
                $msg_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Admin - Secure Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translate3d(0, 40px, 0); }
            to { opacity: 1; transform: translate3d(0, 0, 0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.8s both; }
    </style>
</head>
<body class="bg-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row m-4 animate-fade-in-up">
        
        <div class="w-full md:w-5/12 bg-blue-900 flex flex-col items-center justify-center p-10 text-white text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-800 to-slate-900 opacity-90"></div>
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-48 h-48 bg-white opacity-10 rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <div class="bg-white/10 p-5 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-8 backdrop-blur-sm shadow-lg border border-white/20">
                    <i class="fas fa-user-shield text-4xl text-blue-200"></i>
                </div>
                
                <h2 class="text-3xl font-bold mb-3 tracking-tight">Authorized Only</h2>
                <p class="text-blue-200 font-medium text-lg mb-8">Admin Creation Portal</p>
                
                <div class="bg-blue-800/50 p-4 rounded-xl text-sm text-blue-100 text-left border border-blue-700/50">
                    <p class="mb-2"><i class="fas fa-lock mr-2 text-yellow-400"></i> <strong>Security Active</strong></p>
                    <p class="opacity-80">This session is monitored. Only verified Campus Administrators can access this tool.</p>
                </div>
            </div>
        </div>

        <div class="w-full md:w-7/12 p-8 md:p-12 bg-white flex flex-col justify-center">
            
            <div class="mb-8">
                <h3 class="text-3xl font-bold text-gray-800 mb-2">Create New Admin</h3>
                <p class="text-gray-500">Grant system access to a new staff member.</p>
            </div>

            <?php if($msg): ?>
                <div class="p-4 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium border <?php echo $msg_type == 'success' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100'; ?>">
                    <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> text-lg"></i> 
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Full Name</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-600 transition-colors"><i class="fas fa-id-badge"></i></span>
                        <input type="text" name="name" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="Enter full name" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Username</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-600 transition-colors"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="admin_user" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Email</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-600 transition-colors"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="admin@mct.edu.ph" required>
                        </div>
                    </div>
                </div>

                 <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Phone Number</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-600 transition-colors"><i class="fas fa-phone"></i></span>
                        <input type="text" name="phone" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="0999 123 4567" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Password</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-600 transition-colors"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Confirm Password</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-600 transition-colors"><i class="fas fa-check-double"></i></span>
                            <input type="password" name="confirm_password" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-900/20 transition-all transform active:scale-[0.99] mt-6 flex justify-center items-center gap-2 text-lg">
                    <span>Authorize & Create</span>
                    <i class="fas fa-plus-circle text-lg"></i>
                </button>

            </form>

            <div class="mt-8 text-center text-sm text-gray-500">
                <a href="admin_users.php" class="text-blue-600 font-bold hover:underline flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
                </a>
            </div>
        </div>
    </div>

</body>
</html>