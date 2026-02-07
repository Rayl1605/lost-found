<?php
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

require_once 'db_connect.php'; 
$shoutouts = []; 
if (isset($conn) && !$conn->connect_error) {
    
    $sql = "SELECT finder_name, item_name, message, created_at FROM feedback ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $shoutouts[] = $row;
        }
    }
}
if (empty($shoutouts)) {
    $shoutouts = [
        ['finder_name' => 'Juan Dela Cruz', 'item_name' => 'Exam Paper', 'message' => 'Bro thank you! I seriously thought I was gonna fail.', 'created_at' => date('Y-m-d H:i:s', strtotime('-10 minutes'))],
        ['finder_name' => 'Taylor Swift', 'item_name' => 'Wallet', 'message' => 'Found it near the library entrance. Hope you get it back safe!', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ['finder_name' => 'Maria Clara', 'item_name' => 'ID Lace', 'message' => 'Thanks for keeping it at the guard house.', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ];
}
?>