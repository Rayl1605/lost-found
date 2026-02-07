<?php
session_start();
require_once 'db_connect.php';

// Check if Admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$msg = "";
$msg_type = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_FILES['admin_pic']) && $_FILES['admin_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['admin_pic']['name'];
        $filesize = $_FILES['admin_pic']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $msg = "Error: Only JPG, PNG, and GIF files allowed.";
            $msg_type = "error";
        } elseif ($filesize > 5 * 1024 * 1024) {
            $msg = "Error: File size too large (Max 5MB).";
            $msg_type = "error";
        } else {
            $new_filename = "admin_avatar_" . $admin_id . "." . $ext; 
            if (!file_exists('admin_pictures')) {
                mkdir('admin_pictures', 0777, true);
            }
            
            $upload_path = "admin_pictures/" . $new_filename;

            if (move_uploaded_file($_FILES['admin_pic']['tmp_name'], $upload_path)) {
                $conn->query("UPDATE admin SET avatar = '$new_filename' WHERE admin_id = '$admin_id'");
                header("Location: admin_profile.php?msg=uploaded");
                exit();
            } else {
                $msg = "Failed to upload image. Check folder permissions.";
                $msg_type = "error";
            }
        }
    } 
    elseif (isset($_POST['name'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);

        $sql = "UPDATE admin SET name='$name', email='$email', phone_number='$phone' WHERE admin_id='$admin_id'";
        
        if ($conn->query($sql) === TRUE) {
            $msg = "Profile details updated successfully!";
            $msg_type = "success";
        } else {
            $msg = "Error updating database: " . $conn->error;
            $msg_type = "error";
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'uploaded') {
    $msg = "Profile picture updated successfully!";
    $msg_type = "success";
}

$result = $conn->query("SELECT * FROM admin WHERE admin_id = '$admin_id'");
$admin = $result->fetch_assoc();

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
    <title>Admin Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        .image-upload > input { display: none; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <aside class="w-64 bg-[#0f172a] text-white flex flex-col shadow-2xl transition-all duration-300 hidden md:flex">
        <div class="h-20 flex items-center px-8 border-b border-slate-700">
            <i class="fas fa-shield-alt text-2xl text-blue-500 mr-3"></i>
            <h1 class="text-lg font-bold">Admin Portal</h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="admin_dashboard.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
                <i class="fas fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="admin_lost_items.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
                <i class="fas fa-search w-5"></i> Lost Items
            </a>
            <a href="admin_found_items.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
                <i class="fas fa-hand-holding w-5"></i> Found Items
            </a>
            <a href="admin_users.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
                <i class="fas fa-users w-5"></i> Users
            </a>
            <a href="admin_profile.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-900/50">
                <i class="fas fa-user-circle w-5"></i> My Profile
            </a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <a href="logout.php" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/20 transition group">
                <i class="fas fa-sign-out-alt group-hover:rotate-180 transition-transform duration-300"></i> Logout
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header class="bg-white h-20 shadow-sm flex items-center justify-between px-8 sticky top-0 z-10">
            <h2 class="text-2xl font-bold text-gray-800">My Profile</h2>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-600"><?php echo htmlspecialchars($admin['name']); ?></span>
                
                <div class="w-10 h-10 rounded-full border-2 border-white shadow-sm overflow-hidden bg-gray-200 flex items-center justify-center">
                    <?php if (!empty($admin['avatar']) && file_exists('admin_pictures/' . $admin['avatar'])): ?>
                        <img src="admin_pictures/<?php echo htmlspecialchars($admin['avatar']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user text-gray-500"></i>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="p-8 max-w-5xl mx-auto w-full">
            
            <?php if($msg): ?>
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3 font-medium <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form id="profileForm" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="md:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-blue-600 to-indigo-700"></div>
                        
                        <div class="relative z-10 w-32 h-32 mx-auto mb-4 group">
                            <div class="w-full h-full rounded-full border-4 border-white shadow-lg overflow-hidden bg-white flex items-center justify-center">
                                <?php if (!empty($admin['avatar']) && file_exists('admin_pictures/' . $admin['avatar'])): ?>
                                    <img src="admin_pictures/<?php echo htmlspecialchars($admin['avatar']); ?>?t=<?php echo time(); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-blue-50 flex items-center justify-center text-3xl font-bold text-blue-600">
                                        <?php echo getInitials($admin['name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="absolute bottom-1 right-1 z-20">
                                <label class="image-upload cursor-pointer block transform hover:scale-110 transition">
                                    <span class="bg-slate-800 text-white p-2.5 rounded-full hover:bg-blue-600 transition shadow-md flex items-center justify-center w-9 h-9">
                                        <i class="fas fa-camera text-sm"></i>
                                    </span>
                                    <input type="file" name="admin_pic" accept="image/*" onchange="document.getElementById('profileForm').submit();">
                                </label>
                            </div>
                        </div>

                        <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($admin['name']); ?></h2>
                        <p class="text-gray-500 text-sm mb-4">@<?php echo htmlspecialchars($admin['username']); ?></p>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Administrator</span>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-800">Admin Details</h3>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm transition shadow-lg shadow-blue-500/30">
                                Save Changes
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Full Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Username</label>
                                    <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled class="w-full p-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Phone Number</label>
                                    <input type="text" name="phone" value="<?php echo htmlspecialchars($admin['phone_number']); ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </main>
    </div>
</body>
</html>