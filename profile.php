<?php
session_start();
require_once 'db_connect.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- A. HANDLE TEXT UPDATE ---
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $student_id = isset($_POST['student_id']) ? $conn->real_escape_string($_POST['student_id']) : '';
    $gender = isset($_POST['gender']) ? $conn->real_escape_string($_POST['gender']) : '';

    $sql = "UPDATE users SET fullname='$fullname', email='$email', phone='$phone', student_id='$student_id', gender='$gender' WHERE id='$user_id'";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['username'] = $fullname; // Update session name
        $message = "Profile details updated!";
        $msg_type = "success";
    } else {
        $message = "Error updating details: " . $conn->error;
        $msg_type = "error";
    }

    // --- B. HANDLE PHOTO UPLOAD (UPDATED FOLDER) ---
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $filesize = $_FILES['profile_pic']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate File
        if (!in_array($ext, $allowed)) {
            $message = "Error: Only JPG, PNG, and GIF files allowed.";
            $msg_type = "error";
        } elseif ($filesize > 2 * 1024 * 1024) { 
            $message = "Error: File size is too large (Max 2MB).";
            $msg_type = "error";
        } else {
            // NEW NAMING: student_image_ID.jpg
            $new_filename = "student_image_" . $user_id . "." . $ext; 
            
            // CHANGED PATH: Saving to 'student_picture' folder
            $upload_path = "student_picture/" . $new_filename;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                // Update DB with just the filename
                $conn->query("UPDATE users SET avatar = '$new_filename' WHERE id = '$user_id'");
                $message = "Profile picture updated successfully!";
                $msg_type = "success";
            } else {
                $message = "Error uploading file. Check if 'student_picture' folder exists.";
                $msg_type = "error";
            }
        }
    }
}

// 3. Fetch User Data
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Helper for Initials
function getInitials($name) {
    $words = explode(" ", $name);
    $initials = strtoupper(substr($words[0], 0, 1));
    if (count($words) > 1) {
        $initials .= strtoupper(substr(end($words), 0, 1));
    }
    return $initials;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - MCTI Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        
        .image-upload > input { display: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-sm h-16 flex items-center justify-between px-6 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition"><i class="fas fa-arrow-left"></i></a>
            <span class="font-bold text-lg text-gray-800 tracking-tight">My Profile</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="logout.php" class="text-gray-500 hover:text-red-500 transition"><i class="fas fa-sign-out-alt text-xl"></i> Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-xl <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> flex items-center gap-3">
                <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form id="profileForm" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="md:col-span-1">
                <div class="bg-white rounded-3xl shadow-xl p-8 text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-blue-500 to-purple-600"></div>
                    
                    <div class="relative z-10 w-32 h-32 mx-auto mb-4 group">
                        
                        <div class="w-full h-full rounded-full border-4 border-white shadow-lg overflow-hidden bg-white flex items-center justify-center">
                            <?php if (!empty($user['avatar']) && file_exists('student_picture/' . $user['avatar'])): ?>
                                <img src="student_picture/<?php echo htmlspecialchars($user['avatar']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-blue-100 flex items-center justify-center text-3xl font-bold text-blue-600">
                                    <?php echo getInitials($user['fullname']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="absolute bottom-0 right-0">
                            <label class="image-upload cursor-pointer">
                                <span class="bg-gray-800 text-white p-2 rounded-full hover:bg-blue-600 transition shadow-md block">
                                    <i class="fas fa-camera text-sm"></i>
                                </span>
                                <input type="file" name="profile_pic" accept="image/*" onchange="document.getElementById('profileForm').submit();">
                            </label>
                        </div>

                    </div>

                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user['fullname']); ?></h2>
                    <p class="text-gray-500 text-sm mb-4">@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <div class="inline-block bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold uppercase">
                        <?php echo htmlspecialchars($user['role']); ?>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white rounded-3xl shadow-xl p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Personal Details</h3>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-bold transition shadow-lg shadow-blue-200">
                            Save Changes
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Full Name</label>
                                <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Username (Read Only)</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled class="w-full p-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 cursor-not-allowed">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Phone Number</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+63 900 000 0000" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Student / Employee ID</label>
                                <input type="text" name="student_id" value="<?php echo htmlspecialchars($user['student_id'] ?? ''); ?>" placeholder="ID-12345" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Gender</label>
                                <select name="gender" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($user['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

</body>
</html>