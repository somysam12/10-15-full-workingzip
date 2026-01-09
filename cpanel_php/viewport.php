<?php
require_once 'header.php';
requireLogin();

$config = getAllConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_viewport'])) {
    $keys = [
        'viewport_app_scale', 'viewport_shift_right_dp', 'viewport_shift_down_dp',
        'viewport_container_width_percent', 'viewport_container_height_percent',
        'viewport_black_left_dp', 'layout_preset'
    ];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) setConfig($key, $_POST[$key]);
    }
    $msg = "Viewport settings updated!";
    $config = getAllConfig();
}

$layout_preset = $config['layout_preset'] ?? 'RIGHT_FOCUS';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-white">Viewport & Layout Control</h1>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-expand me-2"></i>Viewport Configuration</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white-50">Layout Preset</label>
                            <select name="layout_preset" class="form-select bg-dark text-white border-secondary">
                                <option value="CENTER_CROP" <?= $layout_preset == 'CENTER_CROP' ? 'selected' : '' ?>>CENTER_CROP</option>
                                <option value="RIGHT_FOCUS" <?= $layout_preset == 'RIGHT_FOCUS' ? 'selected' : '' ?>>RIGHT_FOCUS</option>
                                <option value="LEFT_FOCUS" <?= $layout_preset == 'LEFT_FOCUS' ? 'selected' : '' ?>>LEFT_FOCUS</option>
                                <option value="FULL_FIT" <?= $layout_preset == 'FULL_FIT' ? 'selected' : '' ?>>FULL_FIT</option>
                                <option value="CUSTOM" <?= $layout_preset == 'CUSTOM' ? 'selected' : '' ?>>CUSTOM</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white-50">App Scale</label>
                            <input type="text" name="viewport_app_scale" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['viewport_app_scale'] ?? '1.32') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white-50">Shift Right (DP)</label>
                            <input type="number" name="viewport_shift_right_dp" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['viewport_shift_right_dp'] ?? '140') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white-50">Shift Down (DP)</label>
                            <input type="number" name="viewport_shift_down_dp" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['viewport_shift_down_dp'] ?? '0') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white-50">Black Left (DP)</label>
                            <input type="number" name="viewport_black_left_dp" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['viewport_black_left_dp'] ?? '40') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white-50">Container Width %</label>
                            <input type="number" name="viewport_container_width_percent" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['viewport_container_width_percent'] ?? '92') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white-50">Container Height %</label>
                            <input type="number" name="viewport_container_height_percent" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['viewport_container_height_percent'] ?? '100') ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_viewport" class="btn btn-primary w-100">Save Viewport Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>