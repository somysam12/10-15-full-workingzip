<?php
require_once 'header.php';

$upload_dir = 'uploads/branding/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$error_msg = "";
$success_msg = "";

// Handle Uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_main_logo'])) {
            if (!isset($_FILES['main_logo']) || $_FILES['main_logo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Logo upload failed.");
            }
            $file_ext = pathinfo($_FILES['main_logo']['name'], PATHINFO_EXTENSION);
            $file_name = 'main_logo_' . time() . '.' . $file_ext;
            $target = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['main_logo']['tmp_name'], $target)) {
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $base_path = dirname($_SERVER['SCRIPT_NAME']);
                if ($base_path === DIRECTORY_SEPARATOR || $base_path === '.') $base_path = '';
                $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $base_path . '/' . $target;
                
                $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES ('main_logo_url', ?) ON CONFLICT (config_key) DO UPDATE SET config_value = EXCLUDED.config_value");
                $stmt->execute([$url, $url]);
                $success_msg = "Main logo updated!";
            }
        } elseif (isset($_POST['update_app_icon'])) {
            $app_id = (int)$_POST['app_id'];
            if (!isset($_FILES['app_icon']) || $_FILES['app_icon']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Icon upload failed.");
            }
            $file_ext = pathinfo($_FILES['app_icon']['name'], PATHINFO_EXTENSION);
            $file_name = 'app_icon_' . $app_id . '_' . time() . '.' . $file_ext;
            $target = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['app_icon']['tmp_name'], $target)) {
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $base_path = dirname($_SERVER['SCRIPT_NAME']);
                if ($base_path === DIRECTORY_SEPARATOR || $base_path === '.') $base_path = '';
                $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $base_path . '/' . $target;
                
                $stmt = $pdo->prepare("UPDATE apps SET iconUrl = ? WHERE id = ?");
                $stmt->execute([$url, $app_id]);
                $success_msg = "App icon updated!";
            }
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

$all_config = getAllConfig();
$apps = $pdo->query("SELECT id, app_name, iconUrl FROM apps ORDER BY app_name ASC")->fetchAll();
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-white">App Branding & Icons</h1>

    <?php if ($error_msg): ?><div class="alert alert-danger"><?php echo $error_msg; ?></div><?php endif; ?>
    <?php if ($success_msg): ?><div class="alert alert-success"><?php echo $success_msg; ?></div><?php endif; ?>

    <div class="row">
        <!-- Main Logo Section -->
        <div class="col-md-5 mb-4">
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-dark border-secondary py-3">
                    <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-image me-2"></i>Main Banner Logo</h5>
                </div>
                <div class="card-body text-center">
                    <p class="text-white-50 small mb-4">This logo appears at the top of your Android app.</p>
                    <div class="mb-4 p-4 bg-light rounded shadow-sm d-flex align-items-center justify-content-center" style="min-height: 150px; background-image: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                        <?php if (!empty($all_config['main_logo_url'])): ?>
                            <img src="<?php echo htmlspecialchars($all_config['main_logo_url']); ?>" class="img-fluid" style="max-height: 120px;">
                        <?php else: ?>
                            <div class="text-muted">
                                <i class="fas fa-photo-film fa-3x mb-2"></i><br>
                                <span>No Logo Uploaded</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="border-top border-secondary pt-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white-50 small">Choose Logo File (PNG/JPG)</label>
                            <input type="file" name="main_logo" class="form-control bg-dark text-white border-secondary" accept="image/*" required>
                        </div>
                        <button type="submit" name="update_main_logo" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="fas fa-cloud-arrow-up me-2"></i>Update Main Logo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- App Icons Section -->
        <div class="col-md-7">
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-dark border-secondary py-3">
                    <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-th-large me-2"></i>Individual App Icons</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="small text-uppercase text-white-50 bg-black">
                                <tr>
                                    <th class="border-secondary px-4 py-3">App Name</th>
                                    <th class="border-secondary text-center">Current Icon</th>
                                    <th class="border-secondary px-4">Upload New Icon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($apps)): ?>
                                    <tr><td colspan="3" class="text-center py-5 text-white-50">No apps found. Create an app first in APK Management.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($apps as $app): ?>
                                        <tr>
                                            <td class="px-4">
                                                <div class="text-white fw-bold"><?php echo htmlspecialchars($app['app_name']); ?></div>
                                                <small class="text-white-50">ID: #<?php echo $app['id']; ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-block p-1 bg-secondary rounded shadow-sm">
                                                    <?php if (!empty($app['iconUrl'])): ?>
                                                        <img src="<?php echo htmlspecialchars($app['iconUrl']); ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded bg-dark d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-image text-white-50"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-4">
                                                <form method="POST" enctype="multipart/form-data" class="input-group input-group-sm">
                                                    <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                    <input type="file" name="app_icon" class="form-control bg-dark text-white border-secondary" accept="image/*" required>
                                                    <button type="submit" name="update_app_icon" class="btn btn-info text-white">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
