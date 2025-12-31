<?php
require_once 'header.php';

$all_config = getAllConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_branding'])) {
        setConfig('theme_mode', $_POST['theme_mode']);
        setConfig('theme_locked', isset($_POST['theme_locked']) ? 'yes' : 'no');
        setConfig('splash_text', $_POST['splash_text']);
        setConfig('bg_color', $_POST['bg_color']);
        $msg = "Branding & Theme updated!";
    }
    
    if (isset($_FILES['splash_logo']) && $_FILES['splash_logo']['error'] === 0) {
        move_uploaded_file($_FILES['splash_logo']['tmp_name'], __DIR__ . '/splash_logo.png');
        setConfig('splash_logo_url', 'splash_logo.png');
        $msg = "Splash logo uploaded!";
    }
}

$all_config = getAllConfig();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Theme & Splash Manager</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Theme Control</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Theme Mode</label>
                        <select name="theme_mode" class="form-select">
                            <option value="Light" <?php echo ($all_config['theme_mode'] ?? '') == 'Light' ? 'selected' : ''; ?>>Light</option>
                            <option value="Dark" <?php echo ($all_config['theme_mode'] ?? '') == 'Dark' ? 'selected' : ''; ?>>Dark</option>
                            <option value="System" <?php echo ($all_config['theme_mode'] ?? '') == 'System' ? 'selected' : ''; ?>>System</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="theme_locked" <?php echo ($all_config['theme_locked'] ?? 'no') == 'yes' ? 'checked' : ''; ?>>
                        <label class="form-check-label">Lock Theme (Users can't change)</label>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Welcome Text</label>
                        <input type="text" name="splash_text" class="form-control" value="<?php echo htmlspecialchars($all_config['splash_text'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Background Color</label>
                        <input type="color" name="bg_color" class="form-control form-control-color w-100" value="<?php echo $all_config['bg_color'] ?? '#0A0E27'; ?>">
                    </div>
                    <button type="submit" name="update_branding" class="btn btn-primary">Save Theme Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Splash Assets</h6>
            </div>
            <div class="card-body text-center">
                <h6>Current Splash Logo</h6>
                <img src="splash_logo.png?v=<?php echo time(); ?>" class="img-fluid border mb-3" style="max-height: 150px; background: <?php echo $all_config['bg_color'] ?? '#eee'; ?>;">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="splash_logo" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Upload Splash Logo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
