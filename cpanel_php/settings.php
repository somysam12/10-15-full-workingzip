<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_general'])) {
        setConfig('app_status', isset($_POST['app_status']) ? 'ON' : 'OFF');
        setConfig('maintenance_message', $_POST['maintenance_message']);
        setConfig('force_logout_flag', isset($_POST['force_logout']) ? 'yes' : 'no');
        $msg = "Global Control updated!";
    }
    
    if (isset($_POST['update_version'])) {
        setConfig('latest_version', $_POST['latest_version']);
        setConfig('min_required_version', (int)$_POST['min_required_version']);
        setConfig('update_url', $_POST['update_url']);
        setConfig('update_message', $_POST['update_message']);
        $msg = "Version settings updated!";
    }

    if (isset($_POST['remote_reset'])) {
        setConfig('reset_cache_flag', 'yes');
        setConfig('reset_time', date('Y-m-d H:i:s'));
        $msg = "Remote reset command sent to all apps!";
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/logo.png');
        $msg = "App logo updated!";
    }
}

$all_config = getAllConfig();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Global App Control</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <!-- Global Control -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">🔐 Security & Status</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="app_status" <?php echo ($all_config['app_status'] ?? 'ON') == 'ON' ? 'checked' : ''; ?>>
                        <label class="form-check-label">App Status (ON/OFF)</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Maintenance Message</label>
                        <textarea name="maintenance_message" class="form-control"><?php echo htmlspecialchars($all_config['maintenance_message'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="force_logout" <?php echo ($all_config['force_logout_flag'] ?? 'no') == 'yes' ? 'checked' : ''; ?>>
                        <label class="form-check-label text-danger">Force Logout All Users</label>
                    </div>
                    <button type="submit" name="update_general" class="btn btn-primary w-100">Save Global Control</button>
                </form>
            </div>
        </div>

        <!-- Remote Reset -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">🧹 Remote Reset / Cache</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Apps will clear WebView cache and sessions on next launch.</p>
                <form method="POST" onsubmit="return confirm('Trigger remote reset for all users?')">
                    <button type="submit" name="remote_reset" class="btn btn-warning w-100"><i class="fas fa-broom"></i> Reset Cache & Sessions</button>
                </form>
                <?php if (($all_config['reset_cache_flag'] ?? 'no') == 'yes'): ?>
                    <div class="badge bg-warning text-dark mt-2 w-100">Reset Pending Since: <?php echo $all_config['reset_time']; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <!-- Version Management -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">⬆️ Version & Updates</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latest Version (Name)</label>
                            <input type="text" name="latest_version" class="form-control" value="<?php echo htmlspecialchars($all_config['latest_version'] ?? ''); ?>" placeholder="e.g. 1.2.0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Min Required (Code)</label>
                            <input type="number" name="min_required_version" class="form-control" value="<?php echo htmlspecialchars($all_config['min_required_version'] ?? '1'); ?>" placeholder="e.g. 2">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Update URL</label>
                        <input type="url" name="update_url" class="form-control" value="<?php echo htmlspecialchars($all_config['update_url'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Update Message</label>
                        <textarea name="update_message" class="form-control"><?php echo htmlspecialchars($all_config['update_message'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_version" class="btn btn-success w-100">Update Version Policy</button>
                </form>
            </div>
        </div>
        
        <!-- App Icon -->
        <div class="card shadow mb-4 text-center">
            <div class="card-header py-3 text-start">
                <h6 class="m-0 font-weight-bold text-primary">Main App Icon</h6>
            </div>
            <div class="card-body">
                <img src="logo.png?v=<?php echo time(); ?>" class="logo-preview-big border" alt="Logo">
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="logo" class="form-control mb-2" accept=".png">
                    <button type="submit" class="btn btn-outline-primary w-100">Upload Icon</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
