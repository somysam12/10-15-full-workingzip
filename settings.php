<?php
require_once 'header.php';

$app_type = $_SESSION['app_type'] ?? 'master';
$status_key = ($app_type === 'panel') ? 'panel_app_status' : 'app_status';
$msg_key = ($app_type === 'panel') ? 'panel_maintenance_msg' : 'master_maintenance_msg';

$app_status = getConfig($status_key, 'OFF');
$maintenance_msg = getConfig($msg_key, 'System is under maintenance.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        setConfig($status_key, $_POST['app_status']);
        setConfig($msg_key, $_POST['maintenance_msg']);
        
        // Security Center: Log settings update
        $admin_id = $_SESSION['admin_id'] ?? 0;
        $stmt = $pdo->prepare("INSERT INTO security_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, 'UPDATE_SETTINGS', "Changed maintenance status to " . $_POST['app_status'], $_SERVER['REMOTE_ADDR']]);
        
        $msg = "Settings updated successfully!";
    }
    
    if (isset($_POST['force_logout'])) {
        // Force logout: Reset admin last_login to force re-auth logic if app checks it
        $stmt = $pdo->prepare("UPDATE admins SET last_login = NULL");
        $stmt->execute();
        
        $admin_id = $_SESSION['admin_id'] ?? 0;
        $stmt = $pdo->prepare("INSERT INTO security_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, 'FORCE_LOGOUT_ALL', "Admin triggered global logout", $_SERVER['REMOTE_ADDR']]);
        $msg = "All users session reset triggered!";
    }

    if (isset($_POST['reset_cache'])) {
        // App-specific cache reset via config flag
        setConfig('reset_cache_flag_' . $app_type, time());
        
        $admin_id = $_SESSION['admin_id'] ?? 0;
        $stmt = $pdo->prepare("INSERT INTO security_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, 'RESET_CACHE', "Admin reset in-app cache for $app_type", $_SERVER['REMOTE_ADDR']]);
        $msg = "App cache reset signal sent!";
    }
    
    if (isset($_POST['update_admin'])) {
        $new_user = $_POST['admin_user'];
        $new_pass = $_POST['admin_pass'];
        if (!empty($new_user) && !empty($new_pass)) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET username = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$new_user, $hash, $_SESSION['admin_id']]);
            $_SESSION['username'] = $new_user;
            
            $admin_id = $_SESSION['admin_id'] ?? 0;
            $stmt = $pdo->prepare("INSERT INTO security_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$admin_id, 'UPDATE_ADMIN', "Admin credentials updated", $_SERVER['REMOTE_ADDR']]);
            $msg = "Admin credentials updated successfully";
        }
    }
    if (isset($_POST['update_advanced_settings'])) {
        $advanced_keys = [
            'viewport_app_scale', 'viewport_shift_right_dp', 'viewport_shift_down_dp',
            'viewport_container_width_percent', 'viewport_container_height_percent',
            'viewport_black_left_dp', 'crop_auto_detect_banner', 'crop_min_banner_height_px',
            'css_enable', 'css_zoom_scale', 'css_hide_selectors', 'modes_focus_mode',
            'modes_lock_reveal', 'layout_preset'
        ];
        foreach ($advanced_keys as $key) {
            if (isset($_POST[$key])) {
                setConfig($key, $_POST[$key]);
            }
        }
        $msg = "Advanced settings updated successfully!";
    }
}

$config = getAllConfig();
$layout_preset = $config['layout_preset'] ?? 'RIGHT_FOCUS';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-white"><?php echo ucfirst($app_type); ?> Control Center</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tools me-2"></i>Maintenance Control</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-white-50">App Status</label>
                        <select name="app_status" class="form-select bg-dark text-white border-secondary">
                            <option value="OFF" <?php echo $app_status == 'OFF' ? 'selected' : ''; ?>>Online (Active)</option>
                            <option value="ON" <?php echo $app_status == 'ON' ? 'selected' : ''; ?>>Maintenance Mode (Offline)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Custom Maintenance Message</label>
                        <textarea name="maintenance_msg" class="form-control bg-dark text-white border-secondary" rows="3"><?php echo htmlspecialchars($maintenance_msg); ?></textarea>
                    </div>
                    <button type="submit" name="update_settings" class="btn btn-primary w-100">Save Mode</button>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4" id="security">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-shield-halved me-2"></i>Security Center</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form method="POST">
                        <button type="submit" name="force_logout" class="btn btn-outline-danger w-100 mb-2">
                            <i class="fas fa-user-slash me-2"></i>Force Logout All Users
                        </button>
                        <button type="submit" name="reset_cache" class="btn btn-outline-warning w-100">
                            <i class="fas fa-broom me-2"></i>Reset In-App Caches
                        </button>
                    </form>
                </div>
                <hr class="border-secondary opacity-25">
                <h6 class="text-white-50 small mb-3">Recent Security Logs</h6>
                <div class="table-responsive" style="max-height: 200px;">
                    <table class="table table-dark table-sm table-hover border-secondary">
                        <thead class="small text-muted">
                            <tr>
                                <th>Action</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php
                            $logs = $pdo->query("SELECT action, created_at FROM security_logs ORDER BY id DESC LIMIT 5")->fetchAll();
                            foreach($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['action']; ?></td>
                                    <td><?php echo date('H:i:s', strtotime($log['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-lock me-2"></i>Admin Credentials</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="text-white-50">Username</label>
                        <input type="text" name="admin_user" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50">New Password</label>
                        <input type="password" name="admin_pass" class="form-control bg-dark text-white border-secondary" placeholder="New password" required>
                    </div>
                    <button type="submit" name="update_admin" class="btn btn-warning w-100">Update Credentials</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>