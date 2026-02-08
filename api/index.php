<?php
require_once 'db_connect.php';

// Fetch recent reports for the dashboard table
$sql = "SELECT * FROM found_items ORDER BY date_found DESC LIMIT 5";
$results = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lost & Found</title>
    <link rel="stylesheet" href="/style.css">
    <script src="https://kit.fontawesome.com/your-font-awesome-key.js" crossorigin="anonymous"></script>
</head>
<body class="dashboard-body">
    <header class="main-header">
        <div class="header-left">
            <h2>MCT Lost & Found</h2>
        </div>
        <div class="header-right">
            <span>Welcome Back, <strong>User</strong></span>
            <a href="logout.php" class="logout-btn">Logout <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <main class="dashboard-container">
        <section class="action-selection">
            <h1>What would you like to do?</h1>
            <p>Report a lost item or help return a found one.</p>
            
            <div class="action-grid">
                <div class="action-card">
                    <div class="icon-circle lost-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>I Lost an Item</h3>
                    <a href="report_lost.php" class="action-link">Report Lost Item <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="action-card">
                    <div class="icon-circle found-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3>I Found an Item</h3>
                    <a href="report_found.php" class="action-link">Report Found Item <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <section class="recent-reports">
            <h3>Recent Reports</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th>STATUS</th>
                        <th>DATE</th>
                        <th>LOCATION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($results && $results->num_rows > 0): ?>
                        <?php while($item = $results->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                            <td><span class="status-badge">Reported</span></td>
                            <td><?php echo date('M d', strtotime($item['date_found'])); ?></td>
                            <td><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No recent reports found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>