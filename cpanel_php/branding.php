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
                
                $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES ('main_logo_url', ?) ON DUPLICATE KEY UPDATE config_value = ?");
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
        <div class="col-md-4 mb-4">
            <div class="card bg-dark border-secondary shadow">
                <div class="card-header bg-dark border-secondary text-primary fw-bold">Main Banner Logo</div>
                <div class="card-body text-center">
                    <?php if (!empty($all_config['main_logo_url'])): ?>
                        <div class="mb-3 p-3 bg-white rounded">
                            <img src="<?php echo htmlspecialchars($all_config['main_logo_url']); ?>" class="img-fluid" style="max-height: 100px;">
                        </div>
                    <?php else: ?>
                        <div class="py-4 text-white-50 small">No logo set</div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="main_logo" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" accept="image/*" required>
                        <button type="submit" name="update_main_logo" class="btn btn-primary btn-sm w-100">Upload Main Logo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- App Icons Section -->
        <div class="col-md-8">
            <div class="card bg-dark border-secondary shadow">
                <div class="card-header bg-dark border-secondary text-primary fw-bold">Individual App Icons</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead class="small text-uppercase text-white-50">
                                <tr>
                                    <th class="border-secondary px-4">App Name</th>
                                    <th class="border-secondary text-center">Current Icon</th>
                                    <th class="border-secondary px-4">Update Icon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($apps as $app): ?>
                                    <tr>
                                        <td class="px-4 align-middle text-white fw-bold"><?php echo htmlspecialchars($app['app_name']); ?></td>
                                        <td class="text-center align-middle">
                                            <?php if (!empty($app['iconUrl'])): ?>
                                                <img src="<?php echo htmlspecialchars($app['iconUrl']); ?>" class="rounded border border-secondary" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-white-50 small">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 align-middle">
                                            <form method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                <input type="file" name="app_icon" class="form-control form-control-sm bg-dark text-white border-secondary" accept="image/*" required>
                                                <button type="submit" name="update_app_icon" class="btn btn-info btn-sm text-white">Upload</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
