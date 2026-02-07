<?php
session_start();
require_once 'db_connect.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// --- FETCH ADMIN PROFILE (Name & Avatar) ---
$sql_me = "SELECT name, avatar FROM admin WHERE admin_id = '$admin_id'";
$result_me = $conn->query($sql_me);
$me = $result_me->fetch_assoc();
$my_name = $me['name'];
$my_avatar = $me['avatar'];

// 1. Delete Item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM found_items WHERE id=$id");
    header("Location: admin_found_items.php");
    exit();
}

// 2. Mark as Claimed
if (isset($_GET['claim'])) {
    $id = intval($_GET['claim']);
    $conn->query("UPDATE found_items SET status='Claimed' WHERE id=$id");
    header("Location: admin_found_items.php");
    exit();
}

// Fetch Found Items
$sql = "SELECT * FROM found_items ORDER BY date_found DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Found Items Management</title>
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
            <a href="admin_found_items.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-teal-600 text-white shadow-lg shadow-teal-900/50">
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
            <h2 class="text-2xl font-bold text-gray-800">Found Items Management</h2>
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
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="relative w-full md:w-96">
                    <span class="absolute left-4 top-3 text-gray-400"><i class="fas fa-search"></i></span>
                    <input type="text" placeholder="Search found items..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 shadow-sm">
                </div>
                </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Location Found</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date Found</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($row['item_name']); ?></div>
                                        <div class="text-xs text-gray-400">By: <?php echo htmlspecialchars($row['reporter_name']); ?></div>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td class="p-4 text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt text-teal-400 mr-1"></i> <?php echo htmlspecialchars($row['location']); ?>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($row['date_found']); ?></td>
                                    <td class="p-4">
                                        <?php if ($row['status'] == 'Claimed'): ?>
                                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold shadow-sm border border-blue-200">
                                                Claimed
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-teal-100 text-teal-700 px-3 py-1 rounded-full text-xs font-bold shadow-sm border border-teal-200">
                                                Found
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        
                                        <button onclick='openModal(<?php echo json_encode($row); ?>)' class="text-gray-400 hover:text-blue-600 p-2 rounded-lg hover:bg-blue-50 transition" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <?php if ($row['status'] != 'Claimed'): ?>
                                            <a href="admin_found_items.php?claim=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-teal-600 p-2 rounded-lg hover:bg-teal-50 transition" title="Mark as Claimed" onclick="return confirm('Mark this item as Claimed?')">
                                                <i class="fas fa-hand-holding-heart"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="admin_found_items.php?delete=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition" title="Delete Item" onclick="return confirm('Delete this report permanently?')">
                                            <i class="fas fa-trash"></i>
                                        </a>

                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-500">No found items reported yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="viewModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 transform scale-100 transition-all">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-box-open text-teal-500"></i> Item Details
                </h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="flex flex-col items-center mb-4">
                    <img id="modalImage" src="" alt="Item Image" class="h-40 object-contain rounded-lg shadow-sm border border-gray-200 hidden">
                    <div id="noImageText" class="text-gray-400 italic text-sm py-4 hidden">No image provided</div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Item Name</p>
                        <p id="modalName" class="text-gray-800 font-medium"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Category</p>
                        <p id="modalCategory" class="text-gray-800 font-medium"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Location Found</p>
                        <p id="modalLocation" class="text-gray-800 font-medium"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Date Found</p>
                        <p id="modalDate" class="text-gray-800 font-medium"></p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Description</p>
                    <p id="modalDescription" class="text-gray-700 bg-gray-50 p-3 rounded-lg text-sm mt-1"></p>
                </div>

                <div class="pt-4 flex justify-end">
                    <button onclick="closeModal()" class="bg-gray-100 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-200 font-medium transition">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(data) {
            document.getElementById('modalName').innerText = data.item_name;
            document.getElementById('modalCategory').innerText = data.category;
            document.getElementById('modalLocation').innerText = data.location;
            document.getElementById('modalDate').innerText = data.date_found;
            document.getElementById('modalDescription').innerText = data.description;

            // Handle Image
            const imgElement = document.getElementById('modalImage');
            const noImgText = document.getElementById('noImageText');
            
            if (data.image_path) {
                imgElement.src = 'uploads/' + data.image_path;
                imgElement.classList.remove('hidden');
                noImgText.classList.add('hidden');
            } else {
                imgElement.classList.add('hidden');
                noImgText.classList.remove('hidden');
            }

            document.getElementById('viewModal').classList.remove('hidden');
            document.getElementById('viewModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('viewModal').classList.add('hidden');
            document.getElementById('viewModal').classList.remove('flex');
        }
    </script>
</body>
</html>