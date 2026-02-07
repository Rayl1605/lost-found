$host = 'mysql-2020e1cf-rayllylalmendras-0230.j.aivencloud.com';
$user = 'avnadmin';         
$pass = 'AVNS_9keDpfhiZkvFpPmSCzA'; 
$db   = 'defaultdb';       
$port = '19987';            

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}