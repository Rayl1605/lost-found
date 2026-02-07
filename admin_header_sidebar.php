<div class="top-header">
    <div class="header-brand">
        <img src="logo.png" alt="Logo">
        <div class="header-text">
            MARVELOUSE COLLEGE OF TECHNOLOGY, INC.<br>
            LOST AND FOUND TRACKER
        </div>
    </div>
    <div class="menu-icon" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <a href="admin_dashboard.php" class="nav-item">
        <i class="fas fa-chart-pie"></i> REPORT PAGE
    </a>
    <a href="admin_lost_items.php" class="nav-item">
        <i class="fas fa-umbrella"></i> LOST ITEM MGMT
    </a>
    <a href="admin_found_items.php" class="nav-item">
        <i class="fas fa-box-open"></i> FOUND ITEM MGMT
    </a>
    
    <a href="admin_users.php" class="nav-item">
        <i class="fas fa-users"></i> USER MGMT
    </a>
    <a href="admin_profile.php" class="nav-item">
        <i class="fas fa-user-circle"></i> MY PROFILE
    </a>
    <a href="logout.php" class="nav-item">
        <i class="fas fa-sign-out-alt"></i> LOGOUT
    </a>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        // Improved toggle logic for smoother experience
        if (sidebar.style.display === "block") {
            sidebar.style.display = "none";
        } else {
            sidebar.style.display = "block";
        }
    }
</script>