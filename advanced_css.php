<?php
require_once 'config.php';
requireLogin();

$app_type = $_SESSION['app_type'] ?? 'master';
$config = getAllConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_css'])) {
    $keys = [
        'css_enable', 'css_zoom_scale', 'css_hide_selectors', 
        'modes_focus_mode', 'modes_lock_reveal',
        'crop_auto_detect_banner', 'crop_min_banner_height_px'
    ];
    foreach ($keys as $key) {
        $val = $_POST[$key] ?? 'false';
        if ($val === 'on') $val = 'true';
        setConfig($key, $val);
    }
    header("Location: advanced_css.php?success=1");
    exit();
}

if (isset($_GET['success'])) {
    $msg = "CSS & Advanced settings updated!";
}

require_once 'header.php';
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-code me-2"></i>Stylus-like Injection</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="css_enable" <?= ($config['css_enable'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label text-white-50">Enable CSS Injection</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white-50">Zoom Scale</label>
                            <input type="text" name="css_zoom_scale" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['css_zoom_scale'] ?? '1.15') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white-50">Hide Selectors</label>
                            <input type="text" name="css_hide_selectors" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['css_hide_selectors'] ?? 'header,.top-banner') ?>">
                        </div>
                    </div>
                    <hr class="border-secondary">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="crop_auto_detect_banner" <?= ($config['crop_auto_detect_banner'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label text-white-50">Auto-Detect Banner Crop</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white-50">Min Banner Height (PX)</label>
                            <input type="number" name="crop_min_banner_height_px" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['crop_min_banner_height_px'] ?? '50') ?>">
                        </div>
                    </div>
                    <hr class="border-secondary">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="modes_focus_mode" <?= ($config['modes_focus_mode'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label text-white-50">Focus Mode</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="modes_lock_reveal" <?= ($config['modes_lock_reveal'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label text-white-50">Lock Reveal (Anti-branding)</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update_css" class="btn btn-info w-100 text-white">Save Advanced Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>