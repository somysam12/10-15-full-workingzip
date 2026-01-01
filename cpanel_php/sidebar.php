<?php
$app_type = $_SESSION['app_type'] ?? 'master';
?>
<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary mb-3">
        <h4 class="mb-0 fw-bold text-white"><i class="fas fa-shield-halved me-2"></i> Silent Panel</h4>
        <small class="text-muted"><?php echo ucfirst($app_type); ?> App</small>
    </div>
    <nav>
        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <?php if ($app_type === 'master'): ?>
        <a href="apps.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apps.php' ? 'active' : ''; ?>">
            <i class="fas fa-mobile-screen"></i> APK Management
        </a>
        <a href="categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        <?php endif; ?>

        <?php if ($app_type === 'panel'): ?>
        <a href="panels.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'panels.php' ? 'active' : ''; ?>">
            <i class="fas fa-layer-group"></i> Website Panels
        </a>
        <?php endif; ?>

        <a href="announcements.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn"></i> Announcements
        </a>
        <a href="branding.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'branding.php' ? 'active' : ''; ?>">
            <i class="fas fa-palette"></i> Branding & Icons
        </a>
        <a href="analytics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
        <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>

        <div class="mt-auto px-3 mb-3">
            <hr class="border-secondary">
            <form action="switch_app.php" method="POST">
                <button type="submit" name="switch_type" value="<?php echo $app_type === 'master' ? 'panel' : 'master'; ?>" class="btn btn-outline-info w-100 btn-sm">
                    <i class="fas fa-exchange-alt me-2"></i> Switch to <?php echo $app_type === 'master' ? 'Panel' : 'Master'; ?>
                </button>
            </form>
        </div>

        <div class="px-3">
            <a href="logout.php" class="btn btn-danger w-100 btn-sm py-2">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </nav>
</div>