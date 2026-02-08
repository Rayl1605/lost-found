<?php
// api/index.php
require_once 'db_connect.php';

// Fetch a few recent found items to show on the homepage
$sql = "SELECT * FROM found_items ORDER BY date_found DESC LIMIT 5";
$results = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost and Found System</title>
    <link rel="stylesheet" href="/style.css"> 
</head>
<body>
    <header>
        <h1>Lost and Found Portal</h1>
        <nav>
            <a href="login.php">Login</a> | 
            <a href="register.php">Register</a> | 
            <a href="report_found.php">Report Found Item</a>
        </nav>
    </header>

    <main>
        <h2>Recent Found Items</h2>
        <div class="items-grid">
            <?php if ($results && $results->num_rows > 0): ?>
                <?php while($item = $results->fetch_assoc()): ?>
                    <div class="item-card">
                        <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        <p>Location: <?php echo htmlspecialchars($item['location']); ?></p>
                        <p>Date: <?php echo htmlspecialchars($item['date_found']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No recently found items to display.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Lost and Found System</p>
    </footer>
</body>
</html>