<?php
require_once 'db_connect.php'; 

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $check = $conn->query("SELECT id FROM users WHERE username='$username' OR email='$email'");
        if ($check->num_rows > 0) {
            $error = "Username or Email already taken.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (fullname, username, email, password, role) VALUES ('$fullname', '$username', '$email', '$hashed_password', 'user')";
            
            if ($conn->query($sql) === TRUE) {
                $success = "Account created successfully! Redirecting to login...";
                header("refresh:2;url=login.php"); // Auto redirect after 2 seconds
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row m-4 animate-fade-in-up">
        
        <div class="w-full md:w-5/12 bg-blue-600 flex flex-col items-center justify-center p-10 text-white text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-700 opacity-90"></div>
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-48 h-48 bg-white opacity-10 rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <div class="bg-white/20 p-5 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-8 backdrop-blur-sm shadow-lg">
                    <i class="fas fa-user-plus text-4xl text-white"></i>
                </div>
                
                <h2 class="text-3xl font-bold mb-3 tracking-tight">Join the Community</h2>
                <p class="text-blue-100 font-medium text-lg mb-8">Marvelous College of Technology,Inc.</p>
                
                <div class="space-y-4 text-sm text-blue-50 opacity-90 max-w-xs mx-auto">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center"><i class="fas fa-check"></i></div>
                        <span>Report lost items quickly</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center"><i class="fas fa-check"></i></div>
                        <span>Help others recover belongings</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center"><i class="fas fa-check"></i></div>
                        <span>Get real-time updates</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-7/12 p-8 md:p-12 bg-white flex flex-col justify-center">
            
            <div class="mb-8">
                <h3 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h3>
                <p class="text-gray-500">Fill in your details to get started.</p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium border border-red-100">
                    <i class="fas fa-exclamation-circle text-lg"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium border border-green-100">
                    <i class="fas fa-check-circle text-lg"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-5">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Full Name</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="fullname" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="Juan Dela Cruz" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Username</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="juandelacruz" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Email</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="juan@example.com" required>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Password</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Confirm Password</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-check-double"></i></span>
                            <input type="password" name="confirm_password" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700 font-medium" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-3 mt-2">
                    <div class="flex items-center h-5">
                        <input id="terms" type="checkbox" required class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 accent-blue-600 cursor-pointer">
                    </div>
                    <label for="terms" class="text-sm font-medium text-gray-500 cursor-pointer">
                        I agree to the 
                        <a href="terms.php" target="_blank" class="text-blue-600 hover:underline hover:text-blue-800">Terms of Service</a> 
                        and 
                        <a href="privacy.php" target="_blank" class="text-blue-600 hover:underline hover:text-blue-800">Privacy Policy</a>
                    </label>
                    </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 transition-all transform active:scale-[0.99] mt-4 flex justify-center items-center gap-2 text-lg">
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>

            </form>

            <div class="mt-8 text-center text-sm text-gray-500">
                Already have an account? <a href="login.php" class="text-blue-600 font-bold hover:underline">Sign In</a>
            </div>
        </div>
    </div>

</body>
</html>