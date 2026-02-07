<?php
session_start();
require_once 'db_connect.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// 1. Fetch Current Admin Avatar (For the Header)
$sql_me = "SELECT avatar FROM admin WHERE admin_id = '$admin_id'";
$result_me = $conn->query($sql_me);
$my_data = $result_me->fetch_assoc();
$my_avatar = $my_data['avatar'];

// 2. Total Lost
$sql_lost = "SELECT COUNT(*) as total FROM lost_items";
$result_lost = $conn->query($sql_lost);
$total_lost = $result_lost ? $result_lost->fetch_assoc()['total'] : 0;

// 3. Total Found
$sql_found = "SELECT COUNT(*) as total FROM found_items";
$result_found = $conn->query($sql_found);
$total_found = $result_found ? $result_found->fetch_assoc()['total'] : 0;

// 4. Total Returned (Claimed)
$sql_returned = "SELECT COUNT(*) as total FROM found_items WHERE status = 'Claimed'";
$result_returned = $conn->query($sql_returned);
$total_returned = $result_returned ? $result_returned->fetch_assoc()['total'] : 0;

// 5. Chart Data (Categories)
$cat_labels = [];
$cat_data = [];

$sql_cat = "SELECT category, COUNT(*) as count FROM lost_items GROUP BY category";
$result_cat = $conn->query($sql_cat);

if ($result_cat) {
    while ($row = $result_cat->fetch_assoc()) {
        $cat_labels[] = $row['category'];
        $cat_data[] = $row['count'];     
    }
}

$json_labels = json_encode($cat_labels);
$json_data = json_encode($cat_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="admin_dashboard.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-900/50 transition">
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
            <h2 class="text-2xl font-bold text-gray-800">Overview</h2>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                
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
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Lost</p>
                        <h3 class="text-4xl font-bold text-gray-800"><?php echo $total_lost; ?></h3>
                    </div>
                    <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center text-red-500 shadow-sm">
                        <i class="fas fa-search text-2xl"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Found</p>
                        <h3 class="text-4xl font-bold text-gray-800"><?php echo $total_found; ?></h3>
                    </div>
                    <div class="w-14 h-14 bg-green-50 rounded-full flex items-center justify-center text-green-500 shadow-sm">
                        <i class="fas fa-check text-2xl"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Returned</p>
                        <h3 class="text-4xl font-bold text-gray-800"><?php echo $total_returned; ?></h3>
                    </div>
                    <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 shadow-sm">
                        <i class="fas fa-handshake text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-500"></i> Monthly Reports
                    </h3>
                    <div class="relative h-64">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-purple-500"></i> Item Categories
                    </h3>
                    <div class="relative h-64 flex justify-center">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Current Data'],
                datasets: [
                    { 
                        label: 'Lost Items', 
                        data: [<?php echo $total_lost; ?>], 
                        backgroundColor: '#F87171',
                        borderRadius: 6
                    },
                    { 
                        label: 'Found Items', 
                        data: [<?php echo $total_found; ?>], 
                        backgroundColor: '#34D399',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { position: 'top' } }
            }
        });

        const categoryLabels = <?php echo $json_labels; ?>; 
        const categoryData = <?php echo $json_data; ?>;  

        if (categoryLabels.length === 0) {
            categoryLabels.push("No Data");
            categoryData.push(1);
        }

        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: [
                        '#3B82F6', '#F59E0B', '#10B981', '#6366F1', '#EC4899' 
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } }
                }
            }
        });
    </script>
</body>
</html>