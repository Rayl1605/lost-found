<?php
session_start();
require_once 'db_connect.php';

// 1.Redirect to login.php
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

//2.SAFE HELPER FUNCTIONS
if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array('y' => 'year','m' => 'month','w' => 'week','d' => 'day','h' => 'hour','i' => 'minute','s' => 'second');
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('get_avatar_data')) {
    function get_avatar_data($name) {
        $words = explode(" ", $name);
        $initials = strtoupper(substr($words[0], 0, 1));
        if (count($words) > 1) {
            $initials .= strtoupper(substr(end($words), 0, 1));
        }
        
        $hash = md5($name);
        $colors = ['#d1fae5', '#e0e7ff', '#fce7f3', '#fef3c7', '#e0f2fe']; 
        $text_colors = ['#065f46', '#4338ca', '#be185d', '#92400e', '#075985']; 
        
        $index = hexdec(substr($hash, 0, 2)) % count($colors);
        
        return ['initials' => $initials, 'bg' => $colors[$index], 'text' => $text_colors[$index]];
    }
}

// 3. ROBUST DATABASE QUERIES
$sql_items = "
(SELECT id, item_name, status, date_lost AS report_date, location, 'lost' AS type, 'Unknown' as finder_name FROM lost_items)
UNION
(SELECT id, item_name, status, date_found AS report_date, location, 'found' AS type, 'Someone' AS finder_name FROM found_items)
ORDER BY report_date DESC
LIMIT 10
";

$result_items = false;
if (isset($conn) && !$conn->connect_error) {
    $result_items = $conn->query($sql_items);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }

        .shoutout-widget { height: 600px; display: flex; flex-direction: column; }
        .shoutout-feed { overflow-y: auto; flex-grow: 1; scroll-behavior: smooth; }
        .shoutout-feed::-webkit-scrollbar { width: 6px; }
        .shoutout-feed::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 4px; }

        .modal-overlay { background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.5s ease-out; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-sm h-16 flex items-center justify-between px-6 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 text-white p-2 rounded-lg"><i class="fas fa-search-location"></i></div>
            <span class="font-bold text-lg text-gray-800 tracking-tight">MCT Lost & Found</span>
        </div>
        <div class="flex items-center gap-6">
            <div class="hidden md:flex flex-col text-right mr-2">
                <span class="text-xs text-gray-400 font-bold uppercase">Welcome Back</span>
                <span class="text-sm font-bold text-gray-700 leading-tight"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
            </div>
            
            <a href="profile.php" class="text-gray-400 hover:text-blue-600 transition transform hover:scale-110" title="My Profile">
                <i class="fas fa-user-circle text-3xl"></i>
            </a>
            
            <div class="h-8 w-px bg-gray-200 mx-1"></div>

            <a href="logout.php" class="flex items-center gap-2 text-gray-500 hover:text-red-600 transition font-medium group">
                <span class="hidden md:block group-hover:underline decoration-red-600 underline-offset-4">Logout</span>
                <i class="fas fa-sign-out-alt text-xl group-hover:rotate-180 transition-transform duration-300"></i>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-12 max-w-6xl">
        
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">What would you like to do?</h1>
            <p class="text-gray-500">Report a lost item or help return a found one.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16 max-w-4xl mx-auto">
            <a href="report_lost.php" class="group bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-2 border-transparent hover:border-blue-500 cursor-pointer flex flex-col items-center text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-blue-50 w-32 h-32 rounded-bl-full -mr-8 -mt-8 transition-all group-hover:bg-blue-100"></div>
                <div class="bg-blue-100 text-blue-600 w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fas fa-search text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">I Lost an Item</h2>
                <span class="text-blue-600 font-bold group-hover:underline mt-4">Report Lost Item &rarr;</span>
            </a>

            <a href="report_found.php" class="group bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-2 border-transparent hover:border-teal-500 cursor-pointer flex flex-col items-center text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-teal-50 w-32 h-32 rounded-bl-full -mr-8 -mt-8 transition-all group-hover:bg-teal-100"></div>
                <div class="bg-teal-100 text-teal-600 w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fas fa-hand-holding-heart text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">I Found an Item</h2>
                <span class="text-teal-600 font-bold group-hover:underline mt-4">Report Found Item &rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <h3 class="text-xl font-bold text-gray-800 mb-6 px-2 border-l-4 border-blue-600 pl-3">Recent Reports</h3>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="p-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if ($result_items && $result_items->num_rows > 0): ?>
                                    <?php while($row = $result_items->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-5 font-bold text-gray-800"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                        <td class="p-5">
                                            <?php 
                                                $status = strtolower($row['status']);
                                                $colorClass = match($status) {
                                                    'lost' => 'bg-red-100 text-red-600',
                                                    'found' => 'bg-green-100 text-green-600',
                                                    'claimed' => 'bg-blue-100 text-blue-600',
                                                    default => 'bg-gray-100 text-gray-600'
                                                };
                                            ?>
                                            <span class="<?php echo $colorClass; ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="p-5 text-sm text-gray-500"><?php echo date('M d', strtotime($row['report_date'])); ?></td>
                                        <td class="p-5 text-sm text-gray-500">
                                            <i class="fas fa-map-marker-alt text-gray-300 mr-1"></i>
                                            <?php echo htmlspecialchars($row['location']); ?>
                                        </td>
                                        
                                        <td class="p-5">
                                            <?php if ($status == 'claimed' && $row['type'] == 'found'): ?>
                                                <button onclick="openFeedbackModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['finder_name']); ?>', '<?php echo htmlspecialchars($row['item_name']); ?>')" 
                                                        class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-3 py-1 rounded-md text-xs font-semibold transition">
                                                    <i class="fas fa-heart mr-1"></i> Thanks
                                                </button>
                                            <?php else: ?>
                                                <span class="text-gray-300">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-8 text-center text-gray-400">No recent reports found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="shoutout-widget bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-white">
                        <h3 class="font-bold text-gray-800">Community Love</h3>
                        <span id="shoutoutCount" class="bg-red-100 text-red-500 text-xs font-bold px-2 py-1 rounded-full">0 posts</span>
                    </div>

                    <div id="shoutoutFeed" class="shoutout-feed p-5">
                        <div class="flex justify-center items-center h-full">
                            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div> </div>

    <div id="feedbackModal" class="modal-overlay fixed inset-0 flex items-center justify-center hidden">
        <div class="modal-content bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 m-4 relative">
            <button onclick="closeFeedbackModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-heart text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Send Appreciation</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Say thanks to <span id="modalFinderName" class="font-bold text-gray-800"></span> 
                    for finding your <span id="modalItemName" class="font-bold text-gray-800"></span>.
                </p>
            </div>

            <form id="feedbackForm">
                <input type="hidden" name="report_id" id="modalReportId">
                <input type="hidden" name="finder_name" id="modalFinderNameInput">
                <input type="hidden" name="item_name" id="modalItemNameInput">

                <div class="mb-4">
                    <textarea name="message" id="messageInput" rows="4" required
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                        placeholder="Write something nice..."></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Post to Wall
                </button>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal-overlay fixed inset-0 flex items-center justify-center hidden" style="z-index: 2000;">
        <div class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-2xl p-8 text-center transform transition-all scale-100">
            <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-3xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Awesome!</h3>
            <p class="text-gray-500 mb-6">Thank you! Your appreciation message has been posted to the wall.</p>
            
            <button onclick="closeSuccessModal()" class="w-full bg-green-500 text-white font-bold py-3 rounded-xl hover:bg-green-600 transition shadow-lg shadow-green-200">
                Okay, Great!
            </button>
        </div>
    </div>

    <script>
        // MODAL FUNCTIONS 
        
        function openFeedbackModal(reportId, finderName, itemName) {
            document.getElementById('feedbackModal').classList.remove('hidden');
            document.getElementById('modalFinderName').innerText = finderName;
            document.getElementById('modalItemName').innerText = itemName;
            
            // Fill hidden inputs
            document.getElementById('modalReportId').value = reportId;
            document.getElementById('modalFinderNameInput').value = finderName;
            document.getElementById('modalItemNameInput').value = itemName;
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.add('hidden');
        }

        function openSuccessModal() {
            document.getElementById('successModal').classList.remove('hidden');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
        }

        window.onclick = function(event) {
            const feedbackModal = document.getElementById('feedbackModal');
            const successModal = document.getElementById('successModal');
            
            if (event.target == feedbackModal) closeFeedbackModal();
            if (event.target == successModal) closeSuccessModal();
        }

        //  REALTIME FEED LOGIC 
        function loadFeed() {
            fetch('api_feedback.php?action=fetch')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('shoutoutFeed').innerHTML = data.html;
                    document.getElementById('shoutoutCount').innerText = data.count + " posts";
                })
                .catch(error => console.error('Error fetching feed:', error));
        }

        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('api_feedback.php?action=submit', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('messageInput').value = '';
                    closeFeedbackModal();
                    openSuccessModal();
                    loadFeed();
                } else {
                    alert("Error posting feedback: " + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        loadFeed();
        setInterval(loadFeed, 3000);
    </script>
</body>
</html>