<div class="top-header">
    <div class="header-brand">
        <img src="logo.png" alt="MCT Logo">
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
    <a href="dashboard.php" class="nav-item">
        <i class="fas fa-home"></i> HOME
    </a>
    <a href="profile.php" class="nav-item">
        <i class="fas fa-user-circle"></i> PROFILE
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-umbrella"></i> LOST ITEM
    </a>
    <a href="report_lost.php" class="nav-item">
        <i class="fas fa-file-alt"></i> REPORT LOST ITEM
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-box-open"></i> FOUND ITEM
    </a>
    <a href="report_found.php" class="nav-item">
        <i class="fas fa-file-invoice"></i> REPORT FOUND ITEM
    </a>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        if (sidebar.style.display === "block") {
            sidebar.style.display = "none";
        } else {
            sidebar.style.display = "block";
        }
    }
</script>