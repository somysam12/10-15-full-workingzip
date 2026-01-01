<?php
require_once 'header.php';

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$app_id) {
    header("Location: apps.php");
    exit;
}

// 1. Fetch App Name
$app_stmt = $pdo->prepare("SELECT app_name FROM apps WHERE id = ?");
$app_stmt->execute([$app_id]);
$app_name = $app_stmt->fetchColumn() ?: "Unknown App";

// 2. Handle Upload
$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_version'])) {
    // Increase limits at runtime if possible
    @ini_set('upload_max_filesize', '128M');
    @ini_set('post_max_size', '128M');
    @ini_set('max_execution_time', '300');

    $v_name = $_POST['version_name'] ?? 'New Update';
    
    if (isset($_FILES['apk_file'])) {
        if ($_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/apks/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = pathinfo($_FILES['apk_file']['name'], PATHINFO_EXTENSION);
            $clean_name = preg_replace("/[^a-zA-Z0-9]/", "_", $v_name);
            $file_name = time() . "_" . $clean_name . "." . $file_ext;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_path)) {
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $apk_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $target_path;
                
                try {
                    $pdo->prepare("UPDATE app_versions SET is_latest = FALSE WHERE app_id = ?")->execute([$app_id]);
                    $stmt = $pdo->prepare("INSERT INTO app_versions (app_id, version_name, apk_url, is_latest, version_code, created_at) VALUES (?, ?, ?, TRUE, ?, NOW())");
                    $stmt->execute([$app_id, $v_name, $apk_url, time()]);
                    
                    header("Location: app_details.php?id=$app_id&msg=uploaded&v=" . time());
                    exit;
                } catch (Exception $e) {
                    $error_msg = "Database Error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Could not save file to disk. Check permissions.";
            }
        } else {
            $err_code = $_FILES['apk_file']['error'];
            if ($err_code === 1 || $err_code === 2) {
                $error_msg = "File is too large! Maximum allowed is 128MB. (PHP Error Code: $err_code)";
            } else {
                $error_msg = "File upload failed (PHP Error code: $err_code)";
            }
        }
    } else {
        $error_msg = "No file received. Make sure the file isn't too large for the server.";
    }
}

// 3. Fetch History
$history_stmt = $pdo->prepare("SELECT * FROM app_versions WHERE app_id = ? ORDER BY id DESC");
$history_stmt->execute([$app_id]);
$history = $history_stmt->fetchAll();

if (isset($_GET['msg']) && $_GET['msg'] === 'uploaded') $success_msg = "APK Uploaded successfully!";
?>
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-white">Manage: <?php echo htmlspecialchars($app_name); ?></h1>
        <a href="apps.php" class="btn btn-outline-light btn-sm">Back to Apps</a>
    </div>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card bg-dark border-secondary shadow mb-4">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">New APK Upload</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="text-white-50 small">Display Name</label>
                            <input type="text" name="version_name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. BGMI v1.2" required>
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50 small">Select APK (Max 128MB)</label>
                            <input type="file" name="apk_file" class="form-control bg-dark text-white border-secondary" accept=".apk" required>
                        </div>
                        <button type="submit" name="add_version" class="btn btn-primary w-100">Upload & Activate</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card bg-dark border-secondary shadow">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">
                    Uploaded Files History (<?php echo count($history); ?>)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead class="small text-uppercase text-white-50">
                                <tr>
                                    <th class="border-secondary px-4">Name</th>
                                    <th class="border-secondary">Status</th>
                                    <th class="border-secondary px-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                    <tr><td colspan="3" class="text-center py-5 text-white-50">No uploads found.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($history as $v): ?>
                                    <tr>
                                        <td class="px-4">
                                            <div class="text-white"><?php echo htmlspecialchars($v['version_name']); ?></div>
                                            <small class="text-white-50"><?php echo date('M d, Y - H:i', strtotime($v['created_at'])); ?></small>
                                        </td>
                                        <td class="align-middle">
                                            <?php if ($v['is_latest']): ?>
                                                <span class="badge bg-success">LIVE</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary opacity-50">OLD</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 text-end align-middle">
                                            <a href="<?php echo $v['apk_url']; ?>" target="_blank" class="btn btn-sm btn-info">Download</a>
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