<?php
// sidebar.php
?>
<div class="col-md-3 col-lg-2 sidebar">
    <div class="text-center mb-4">
        <h4><i class="fas fa-gamepad"></i> Silent Panel</h4>
    </div>
    <nav>
        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="panels.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'panels.php' ? 'active' : ''; ?>"><i class="fas fa-list"></i> Manage Panels</a>
        <a href="announcements.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> App Control</a>
        <a href="branding.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'branding.php' ? 'active' : ''; ?>"><i class="fas fa-paint-brush"></i> Theme & Splash</a>
    </nav>
</div>
