<?php
require_once 'header.php';

$app_id = $_GET['id'] ?? null;
if (!$app_id) header("Location: apps.php");

$app = $pdo->prepare("SELECT * FROM apps WHERE id = ?");
$app->execute([$app_id]);
$app_data = $app->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_version'])) {
    $v_name = $_POST['version_name'];
    $v_code = $_POST['version_code'];
    $apk_url = $_POST['apk_url'];

    // Handle File Upload
    if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/apks/';
        $file_name = time() . '_' . basename($_FILES['apk_file']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_file)) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
            $apk_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $target_file;
        }
    }
    
    $pdo->prepare("UPDATE app_versions SET is_latest = FALSE WHERE app_id = ?")->execute([$app_id]);
    $stmt = $pdo->prepare("INSERT INTO app_versions (app_id, version_name, version_code, apk_url, is_latest) VALUES (?, ?, ?, ?, TRUE)");
    $stmt->execute([$app_id, $v_name, $v_code, $apk_url]);
    $msg = "Version added successfully";
}

$versions = $pdo->prepare("SELECT * FROM app_versions WHERE app_id = ? ORDER BY version_code DESC");
$versions->execute([$app_id]);
$version_list = $versions->fetchAll();
?>
<h1 class="h3 mb-4">Manage <?php echo htmlspecialchars($app_data['app_name']); ?></h1>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header">App Details</div>
            <div class="card-body">
                <p><strong>Package:</strong> <?php echo $app_data['package_name']; ?></p>
                <p><strong>Status:</strong> <?php echo $app_data['is_enabled'] ? 'Active' : 'Disabled'; ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between">
                <span>Versions</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVersionModal">Add Version</button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Version</th><th>Code</th><th>Latest</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($version_list as $v): ?>
                        <tr>
                            <td><?php echo $v['version_name']; ?></td>
                            <td><?php echo $v['version_code']; ?></td>
                            <td><?php echo $v['is_latest'] ? '✅' : ''; ?></td>
                            <td><a href="<?php echo $v['apk_url']; ?>" class="btn btn-sm btn-outline-primary">Link</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header"><h5>Add New Version / Upload APK</h5></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Version Name</label>
                        <input type="text" name="version_name" class="nav-control form-control mb-2" placeholder="e.g. 1.0.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Version Code</label>
                        <input type="number" name="version_code" class="form-control mb-2" placeholder="e.g. 2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload APK File</label>
                        <input type="file" name="apk_file" class="form-control mb-2" accept=".apk">
                        <div class="text-muted small">Or provide external URL below</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External APK URL</label>
                        <input type="text" name="apk_url" class="form-control" placeholder="https://site.com/app.apk">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal">Cancel</button>
                    <button type="submit" name="add_version" class="btn btn-primary">Upload & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>