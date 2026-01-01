<?php
require_once 'header.php';
// Settings Management
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach($_POST['config'] as $key => $val) {
        setConfig($key, $val);
    }
    $msg = "Settings updated successfully";
}
$configs = getAllConfig();
?>
<h1 class="h3 mb-4">App Configuration</h1>
<div class="card shadow">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label>App Name</label>
                <input type="text" name="config[app_title]" class="form-control" value="<?php echo htmlspecialchars($configs['app_title'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Maintenance Message</label>
                <textarea name="config[maintenance_message]" class="form-control"><?php echo htmlspecialchars($configs['maintenance_message'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Configuration</button>
        </form>
    </div>
</div>
<?php require_once 'footer.php'; ?>