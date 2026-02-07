<?php
require_once 'db_connect.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila'); 

// --- HELPER FUNCTIONS ---
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $ago = new DateTime($datetime, new DateTimeZone('Asia/Manila'));
    
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array('y' => 'year','m' => 'month','w' => 'week','d' => 'day','h' => 'hour','i' => 'minute','s' => 'second');
    foreach ($string as $k => &$v) {
        if ($diff->$k) { $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : ''); } 
        else { unset($string[$k]); }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function get_avatar_data($name) {
    $words = explode(" ", $name);
    $initials = strtoupper(substr($words[0], 0, 1));
    if (count($words) > 1) { $initials .= strtoupper(substr(end($words), 0, 1)); }
    $hash = md5($name);
    $colors = ['#d1fae5', '#e0e7ff', '#fce7f3', '#fef3c7', '#e0f2fe']; 
    $text_colors = ['#065f46', '#4338ca', '#be185d', '#92400e', '#075985'];
    $index = hexdec(substr($hash, 0, 2)) % count($colors);
    return ['initials' => $initials, 'bg' => $colors[$index], 'text' => $text_colors[$index]];
}

$action = $_GET['action'] ?? '';

// 1. SUBMIT FEEDBACK
if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = $_POST['report_id'];
    $finder_name = $conn->real_escape_string($_POST['finder_name']);
    $item_name = $conn->real_escape_string($_POST['item_name']);
    $message = $conn->real_escape_string($_POST['message']);
    $created_at = date('Y-m-d H:i:s'); 
    $sql = "INSERT INTO feedback (report_id, finder_name, item_name, message, created_at) 
            VALUES ('$report_id', '$finder_name', '$item_name', '$message', '$created_at')";
    
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

// 2. FETCH FEED (For Realtime Updates)
if ($action === 'fetch') {
    $html = '';
    $sql = "SELECT finder_name, item_name, message, created_at FROM feedback ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $avatar = get_avatar_data($row['finder_name']);
            $time_ago = time_elapsed_string($row['created_at']); // This now uses the Helper fixed for Manila Time
            $safe_finder = htmlspecialchars($row['finder_name']);
            $safe_item = htmlspecialchars($row['item_name']);
            $safe_message = htmlspecialchars($row['message']);

            $html .= '
            <div class="flex gap-3 mb-6 items-start animate-fade-in">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0" 
                     style="background-color: '.$avatar['bg'].'; color: '.$avatar['text'].';">
                    '.$avatar['initials'].'
                </div>
                <div class="flex-grow">
                    <div class="text-xs text-gray-500 mb-1">
                        <span class="font-bold text-gray-800">'.$safe_finder.'</span>
                        returned
                        <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-bold">'.$safe_item.'</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-tr-lg rounded-br-lg rounded-bl-lg border-l-2 border-green-400 text-sm text-gray-600 italic">
                        "'.$safe_message.'"
                    </div>
                    <span class="text-[10px] text-gray-400 block text-right mt-1">'.$time_ago.'</span>
                </div>
            </div>';
        }
    } else {
        $html = '<p class="text-center text-gray-400 text-sm mt-10">No shoutouts yet.</p>';
    }

    echo json_encode(['html' => $html, 'count' => $result->num_rows]);
    exit;
}
?>