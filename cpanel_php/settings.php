<?php
require_once 'header.php';
require_once 'config.php';

$app_type = $_SESSION['app_type'] ?? 'master';
$status_key = ($app_type === 'panel') ? 'panel_app_status' : 'app_status';
$msg_key = ($app_type === 'panel') ? 'panel_maintenance_msg' : 'maintenance_msg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        setConfig($status_key, $_POST['app_status']);
        setConfig($msg_key, $_POST['maintenance_msg']);
        $msg = "Settings updated successfully!";
    }
    
    if (isset($_POST['update_admin'])) {
        $new_user = $_POST['admin_user'];
        $new_pass = $_POST['admin_pass'];
        if (!empty($new_user) && !empty($new_pass)) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET username = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$new_user, $hash, $_SESSION['admin_id']]);
            $_SESSION['username'] = $new_user;
            $msg = "Admin credentials updated successfully";
        }
    }
}

$app_status = getConfig($status_key, 'ON');
$maintenance_msg = getConfig($msg_key, 'System is under maintenance.');
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?php echo ucfirst($app_type); ?> Settings</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Maintenance Control (<?php echo ucfirst($app_type); ?>)</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">App Status</label>
                        <select name="app_status" class="form-select">
                            <option value="ON" <?php echo $app_status == 'ON' ? 'selected' : ''; ?>>Online</option>
                            <option value="OFF" <?php echo $app_status == 'OFF' ? 'selected' : ''; ?>>Maintenance (Offline)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Maintenance Message</label>
                        <textarea name="maintenance_msg" class="form-control" rows="3"><?php echo htmlspecialchars($maintenance_msg); ?></textarea>
                    </div>
                    <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Admin Access</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="admin_user" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="password" name="admin_pass" class="form-control" placeholder="New password" required>
                    </div>
                    <button type="submit" name="update_admin" class="btn btn-warning">Update Admin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>