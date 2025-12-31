<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_general'])) {
        setConfig('app_enabled', isset($_POST['app_enabled']) ? 'true' : 'false');
        $msg = "General settings updated!";
    }
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/logo.png');
        $msg = "Logo uploaded successfully! The new logo is now active for all APK users.";
    }
}
$app_enabled = getConfig('app_enabled', 'true') === 'true';
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Application Settings</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="app_enabled" <?php echo $app_enabled ? 'checked' : ''; ?>>
                        <label class="form-check-label h5 ms-2">App Active</label>
                        <p class="text-muted small mt-2">When disabled, app will show maintenance message.</p>
                    </div>
                    <button type="submit" name="update_general" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow mb-4 text-center">
            <div class="card-header py-3 text-start">
                <h6 class="m-0 font-weight-bold text-primary">Logo Branding</h6>
            </div>
            <div class="card-body">
                <img src="logo.png?v=<?php echo time(); ?>" class="logo-preview-big border" alt="Logo">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="logo" class="form-control" accept=".png">
                        <p class="small text-muted mt-2">Upload a PNG file to replace the current app logo.</p>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Upload Logo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
