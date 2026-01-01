<?php
// sidebar.php
?>
<div class="sidebar-brand">
    <i class="fas fa-shield-halved"></i> SILENT PANEL
</div>
<nav class="mt-3">
    <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="apps.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apps.php' ? 'active' : ''; ?>">
        <i class="fas fa-mobile-screen"></i> APK Management
    </a>
    <a href="categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
        <i class="fas fa-tags"></i> Categories
    </a>
    <a href="panels.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'panels.php' ? 'active' : ''; ?>">
        <i class="fas fa-layer-group"></i> Website Panels
    </a>
    <a href="announcements.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>">
        <i class="fas fa-bullhorn"></i> Announcements
    </a>
    <a href="analytics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Analytics
    </a>
    <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i> Settings
    </a>
    <hr class="mx-3" style="color: rgba(255,255,255,0.3)">
    <a href="logout.php" class="nav-link text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</nav>