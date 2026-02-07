<?php
session_start();
require_once 'db_connect.php';
require_once 'email_helper.php'; 

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// --- 0. FETCH ADMIN PROFILE (Name & Avatar) ---
$sql_me = "SELECT name, avatar FROM admin WHERE admin_id = '$admin_id'";
$result_me = $conn->query($sql_me);
$me = $result_me->fetch_assoc();
$my_name = $me['name'];
$my_avatar = $me['avatar'];

$msg = "";
$msg_type = "";

// 1. Delete Item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM lost_items WHERE id=$id");
    header("Location: admin_lost_items.php");
    exit();
}

// 2. Mark as Found
if (isset($_GET['found'])) {
    $id = intval($_GET['found']);
    $conn->query("UPDATE lost_items SET status='Found' WHERE id=$id");
    header("Location: admin_lost_items.php");
    exit();
}

// 3. SEND EMAIL NOTIFICATION
if (isset($_GET['notify'])) {
    $item_id = intval($_GET['notify']);

    // Fetch item details AND user email by joining tables
    $sql_user = "SELECT l.item_name, l.reporter_name, u.email, u.fullname 
                 FROM lost_items l 
                 JOIN users u ON l.user_id = u.id 
                 WHERE l.id = $item_id";
    
    $user_res = $conn->query($sql_user);

    if ($user_res->num_rows > 0) {
        $data = $user_res->fetch_assoc();
        $email = $data['email'];
        $name = $data['fullname']; 
        $item = $data['item_name'];

        // Construct the Email Content
        $subject = "Update on your Lost Item: " . $item;
        $body = "
            <h3>Hello $name,</h3>
            <p>This is a notification from the MCT Lost & Found Admin.</p>
            <p>We are writing to follow up on the item you reported lost: <strong>$item</strong>.</p>
            <p>Please visit the Student Affairs Office (SAO) for more information or check your dashboard status.</p>
            <br>
            <p>Best regards,<br>MCT Lost & Found Team</p>
        ";

        $result = sendEmailNotification($email, $subject, $body);

        if ($result === true) {
            $msg = "Email notification sent to $email!";
            $msg_type = "success";
        } else {
            $msg = "Error sending email. Check server/SMTP settings.";
            $msg_type = "error";
        }
    } else {
        $msg = "Error: User email not found. Was this reported by a Guest?";
        $msg_type = "error";
    }
}

// Fetch Lost Items
$sql = "SELECT * FROM lost_items ORDER BY date_lost DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost Items Management</title>
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
            <a href="admin_lost_items.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-900/50">
                <i class="fas fa-search w-5"></i> Lost Items
            </a>
            <a href="admin_found_items.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
                <i class="fas fa-hand-holding w-5"></i> Found Items
            </a>
            <a href="admin_users.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
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
            <h2 class="text-2xl font-bold text-gray-800">Lost Items Management</h2>
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
        </header>

        <main class="p-8">
            
            <?php if ($msg): ?>
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3 font-medium <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                
                <div class="relative w-full md:w-96">
                    <span class="absolute left-4 top-3 text-gray-400"><i class="fas fa-search"></i></span>
                    <input type="text" placeholder="Search lost items..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                </div>

                <a href="print_lost_items.php" target="_blank" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-slate-900/20 transition flex items-center gap-2 font-bold text-sm">
                    <i class="fas fa-print"></i> Print Report
                </a>

            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Item Name</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Category</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Location</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Date Lost</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($row['item_name']); ?></div>
                                        <div class="text-xs text-gray-400">Rep: <?php echo htmlspecialchars($row['reporter_name']); ?></div>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td class="p-4 text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt text-red-400 mr-1"></i> <?php echo htmlspecialchars($row['location']); ?>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($row['date_lost']); ?></td>
                                    <td class="p-4">
                                        <?php if ($row['status'] == 'Found'): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold shadow-sm border border-green-200">Found</span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold shadow-sm border border-red-200">Lost</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        
                                        <a href="admin_lost_items.php?notify=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-yellow-500 p-2 rounded-lg hover:bg-yellow-50 transition" title="Notify Student">
                                            <i class="fas fa-envelope"></i>
                                        </a>

                                        <?php if ($row['status'] != 'Found'): ?>
                                            <a href="admin_lost_items.php?found=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition" title="Mark as Found" onclick="return confirm('Mark this item as Found?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="admin_lost_items.php?delete=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition" title="Delete Report" onclick="return confirm('Delete this report permanently?')">
                                            <i class="fas fa-trash"></i>
                                        </a>

                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-500">No lost items reported yet.</td>
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