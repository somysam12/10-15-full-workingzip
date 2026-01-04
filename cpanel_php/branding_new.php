<?php
require_once 'config.php';
requireLogin();

$app_id = isset($_GET['app_id']) ? (int)$_GET['app_id'] : 0;
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branding'])) {
    try {
        $target_app_id = (int)$_POST['target_app_id'];
        
        // Handle Logo Upload
        if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/branding/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = pathinfo($_FILES['app_logo']['name'], PATHINFO_EXTENSION);
            $file_name = "logo_" . $target_app_id . "_" . time() . "." . $file_ext;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['app_logo']['tmp_name'], $target_path)) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                $logo_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/' . $target_path;
                
                $stmt = $pdo->prepare("UPDATE apps SET iconUrl = ? WHERE id = ?");
                $stmt->execute([$logo_url, $target_app_id]);
                $success_msg = "Logo updated successfully!";
            } else {
                throw new Exception("Failed to move uploaded logo.");
            }
        }
        
        // Update App Name
        if (isset($_POST['app_name'])) {
            $stmt = $pdo->prepare("UPDATE apps SET app_name = ? WHERE id = ?");
            $stmt->execute([$_POST['app_name'], $target_app_id]);
            $success_msg = "Branding updated successfully!";
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

// Get all apps for the selector
$apps_stmt = $pdo->query("SELECT id, app_name, iconUrl FROM apps ORDER BY app_name ASC");
$all_apps = $apps_stmt->fetchAll();

// Get selected app details
$current_app = null;
if ($app_id) {
    foreach ($all_apps as $a) {
        if ($a['id'] == $app_id) {
            $current_app = $a;
            break;
        }
    }
}

require_once 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 text-white">Branding & Logo Management</h1>
            <p class="text-white-50">Customize the appearance of your Master and Panel applications.</p>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $success_msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $error_msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card bg-dark border-secondary shadow mb-4">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">Select Application</div>
                <div class="card-body">
                    <form method="GET">
                        <select name="app_id" class="form-select bg-dark text-white border-secondary mb-3" onchange="this.form.submit()">
                            <option value="">-- Choose App --</option>
                            <?php foreach ($all_apps as $app): ?>
                                <option value="<?php echo $app['id']; ?>" <?php echo ($app_id == $app['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($app['app_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($current_app): ?>
        <div class="col-md-8">
            <div class="card bg-dark border-secondary shadow">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">
                    Edit Branding: <?php echo htmlspecialchars($current_app['app_name']); ?>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="target_app_id" value="<?php echo $current_app['id']; ?>">
                        
                        <div class="row align-items-center mb-4">
                            <div class="col-auto">
                                <div class="rounded bg-dark border border-secondary d-flex align-items-center justify-content-center overflow-hidden" style="width: 120px; height: 120px;">
                                    <?php if ($current_app['iconUrl']): ?>
                                        <img src="<?php echo htmlspecialchars($current_app['iconUrl']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-image fa-3x text-white-50"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <label class="text-white-50 small mb-2 d-block">Change App Logo</label>
                                <input type="file" name="app_logo" class="form-control bg-dark text-white border-secondary" accept="image/*">
                                <small class="text-info mt-2 d-block">Recommended size: 512x512 PNG</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-white-50 small mb-2 d-block">Application Display Name</label>
                            <input type="text" name="app_name" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($current_app['app_name']); ?>" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update_branding" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Save Branding Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="col-md-8">
            <div class="card bg-dark border-secondary shadow d-flex align-items-center justify-content-center p-5 text-center">
                <div class="text-white-50">
                    <i class="fas fa-arrow-left fa-3x mb-3"></i>
                    <h4>Select an application to manage its branding.</h4>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
