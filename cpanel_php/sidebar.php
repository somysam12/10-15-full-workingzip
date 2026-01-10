<?php
$app_type = $_SESSION['app_type'] ?? 'master';
?>
<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary border-opacity-25 mb-3">
        <?php 
        $main_logo = getConfig('main_logo_url');
        if ($main_logo): ?>
            <img src="<?php echo htmlspecialchars($main_logo); ?>" class="img-fluid mb-2" style="max-height: 45px; filter: drop-shadow(0 0 8px var(--accent-glow));">
        <?php else: ?>
            <h4 class="mb-0 fw-bold text-white"><i class="fas fa-shield-halved text-primary me-2"></i> Silent Panel</h4>
        <?php endif; ?>
        <div class="mt-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill small">
                <i class="fas fa-crown me-1 small"></i> <?php echo strtoupper($app_type); ?>
            </span>
        </div>
    </div>
    <nav class="d-flex flex-column h-100">
        <div class="nav-links flex-grow-1">
            <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            
            <?php if ($app_type === 'master'): ?>
            <a href="apps.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apps.php' ? 'active' : ''; ?>">
                <i class="fas fa-mobile-screen"></i> APK Management
            </a>
            <a href="categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a href="app_logos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'app_logos.php' ? 'active' : ''; ?>">
                <i class="fas fa-image"></i> App Logos
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
            
            <a href="in_app_logo.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'in_app_logo.php' ? 'active' : ''; ?>">
                <i class="fas fa-image"></i> In-App Logo
            </a>

            <a href="viewport.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'viewport.php' ? 'active' : ''; ?>">
                <i class="fas fa-expand"></i> Viewport Control
            </a>

            <a href="advanced_css.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'advanced_css.php' ? 'active' : ''; ?>">
                <i class="fas fa-code"></i> Advanced CSS
            </a>

            <a href="keys.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'keys.php' ? 'active' : ''; ?>">
                <i class="fas fa-key"></i> License Keys
            </a>

            <a href="features.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'features.php' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Pro Features
            </a>

            <?php if ($app_type === 'master'): ?>
            <a href="analytics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
            <?php endif; ?>

            <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>

        <div class="sidebar-footer px-3 mt-auto pb-4">
            <hr class="border-secondary opacity-25">
            <form action="switch_app.php" method="POST" class="mb-3">
                <button type="submit" name="switch_type" value="<?php echo $app_type === 'master' ? 'panel' : 'master'; ?>" class="btn btn-outline-info w-100 py-3 d-flex align-items-center justify-content-center">
                    <i class="fas fa-sync-alt me-2"></i> 
                    <span>Switch to <?php echo $app_type === 'master' ? 'Panel' : 'Master'; ?></span>
                </button>
            </form>
            <a href="logout.php" class="btn btn-danger w-100 btn-sm py-2">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </nav>
</div>