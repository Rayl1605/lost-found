<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

//FETCH ADMIN PROFILE (Name & Avatar)
$sql_me = "SELECT name, avatar FROM admin WHERE admin_id = '$admin_id'";
$result_me = $conn->query($sql_me);
$me = $result_me->fetch_assoc();
$my_name = $me['name'];
$my_avatar = $me['avatar'];

// 1. DELETE USER FUNCTIONALITY
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: admin_users.php");
    exit();
}

// 2. DELETE ADMIN FUNCTIONALITY 
if (isset($_GET['delete_admin'])) {
    $id = intval($_GET['delete_admin']);
    // Prevent deleting yourself
    if($id != $_SESSION['admin_id']) {
        $conn->query("DELETE FROM admin WHERE admin_id=$id");
    }
    header("Location: admin_users.php");
    exit();
}

// 3. FETCH ALL USERS AND ADMINS
$sql = "
    (SELECT id, fullname, username, email, role, created_at as joined_date, 'user' as type FROM users)
    UNION ALL
    (SELECT admin_id as id, name as fullname, username, email, 'admin' as role, CURRENT_TIMESTAMP as joined_date, 'admin' as type FROM admin)
    ORDER BY joined_date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
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
            <a href="admin_users.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-900/50">
                <i class="fas fa-users w-5"></i> Users
            </a>

            <a href="admin_profile.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
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
            <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
            
            <div class="flex items-center gap-4">
                
                <a href="add_admin.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg shadow-blue-500/30 transition flex items-center gap-2 transform hover:scale-105">
                    <i class="fas fa-user-plus"></i> <span class="hidden sm:inline">New Admin</span>
                </a>

                <div class="h-8 w-px bg-gray-200"></div>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-600"><?php echo htmlspecialchars($my_name); ?></span>
                    
                    <a href="admin_profile.php" class="w-10 h-10 rounded-full border-2 border-white shadow-sm overflow-hidden bg-gray-200 flex items-center justify-center hover:ring-2 hover:ring-blue-500 transition">
                        <?php if (!empty($my_avatar) && file_exists('admin_pictures/' . $my_avatar)): ?>
                            <img src="admin_pictures/<?php echo htmlspecialchars($my_avatar); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-user text-gray-500"></i>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </header>

        <main class="p-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">User Details</th>
                                <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-5">
                                        <div class="font-bold text-gray-800 text-base"><?php echo htmlspecialchars($row['fullname']); ?></div>
                                        <div class="text-xs text-gray-400 mt-0.5">@<?php echo htmlspecialchars($row['username']); ?> • <?php echo htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td class="p-5">
                                        <?php if (strtolower($row['role']) == 'admin'): ?>
                                            <span class="bg-purple-100 text-purple-600 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider shadow-sm border border-purple-200">Admin</span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider border border-gray-200">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5 text-sm text-gray-500 font-medium">
                                        <?php echo date('M d, Y', strtotime($row['joined_date'])); ?>
                                    </td>
                                    <td class="p-5 text-right">
                                        <?php if ($row['type'] == 'admin'): ?>
                                            <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                                                <a href="admin_users.php?delete_admin=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-red-500 transition p-2" title="Delete Admin" onclick="return confirm('Are you sure you want to delete this ADMIN account?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-300 text-xs italic">You</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="admin_users.php?delete_user=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-red-500 transition p-2" title="Delete User" onclick="return confirm('Are you sure you want to delete this user?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-500">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>