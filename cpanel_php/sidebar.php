<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary mb-3">
        <h4 class="mb-0 fw-bold text-white"><i class="fas fa-shield-halved me-2"></i> Silent Panel</h4>
    </div>
    <nav>
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
        <div class="mt-4 px-3">
            <a href="logout.php" class="btn btn-danger w-100 btn-sm py-2">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </nav>
</div>