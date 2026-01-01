<?php
require_once 'header.php';

$app_id = $_GET['id'] ?? null;
if (!$app_id) header("Location: apps.php");

$app = $pdo->prepare("SELECT * FROM apps WHERE id = ?");
$app->execute([$app_id]);
$app_data = $app->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_version'])) {
    $v_name = $_POST['version_name'];
    $v_code = time(); // Auto-generate code
    $apk_url = "";

    if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/apks/';
        $file_name = time() . '_' . basename($_FILES['apk_file']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_file)) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
            $apk_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $target_file;
        }
    }
    
    if ($apk_url) {
        $pdo->prepare("UPDATE app_versions SET is_latest = FALSE WHERE app_id = ?")->execute([$app_id]);
        $stmt = $pdo->prepare("INSERT INTO app_versions (app_id, version_name, version_code, apk_url, is_latest) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->execute([$app_id, $v_name, $v_code, $apk_url]);
        $msg = "APK uploaded successfully";
    } else {
        $error = "Failed to upload APK";
    }
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
                        <label class="form-label">APK Name</label>
                        <input type="text" name="version_name" class="form-control" placeholder="e.g. BGMI v1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload APK File</label>
                        <input type="file" name="apk_file" class="form-control" accept=".apk" required id="apkFileInput">
                    </div>
                    <div class="progress d-none mb-3" id="apkUploadProgress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_version" class="btn btn-primary" id="uploadApkBtn">Upload & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('form[enctype="multipart/form-data"]').onsubmit = function(e) {
    const fileInput = document.getElementById('apkFileInput');
    if (fileInput.files.length > 0) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('add_version', '1');
        
        const xhr = new XMLHttpRequest();
        const progressBar = document.getElementById('apkUploadProgress');
        const progressInner = progressBar.querySelector('.progress-bar');
        const uploadBtn = document.getElementById('uploadApkBtn');
        
        progressBar.classList.remove('d-none');
        uploadBtn.disabled = true;
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressInner.style.width = percent + '%';
                progressInner.innerText = percent + '%';
            }
        });
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                location.reload();
            }
        };
        
        xhr.open('POST', window.location.href, true);
        xhr.send(formData);
    }
};
</script>
<?php require_once 'footer.php'; ?>