<?php
require_once 'header.php';

$app_id = $_GET['id'] ?? null;
if (!$app_id) {
    header("Location: apps.php");
    exit;
}

$app = $pdo->prepare("SELECT * FROM apps WHERE id = ?");
$app->execute([$app_id]);
$app_data = $app->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_version'])) {
    $v_name = $_POST['version_name'];
    $apk_url = "";

    if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/apks/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $_FILES['apk_file']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_file)) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
            $apk_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $target_file;
        }
    }
    
    if ($apk_url) {
        try {
            $pdo->beginTransaction();
            
            // Explicitly set is_latest = FALSE for all previous versions of this app
            $pdo->prepare("UPDATE app_versions SET is_latest = FALSE WHERE app_id = ?")->execute([$app_id]);
            
            // Insert the new version
            $stmt = $pdo->prepare("INSERT INTO app_versions (app_id, version_name, apk_url, is_latest, version_code, created_at) VALUES (?, ?, ?, TRUE, ?, CURRENT_TIMESTAMP)");
            $stmt->execute([$app_id, $v_name, $apk_url, time()]);
            
            $pdo->commit();
            $msg = "APK Uploaded successfully!";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Upload failed. Check file size or folder permissions.";
    }
}

// Fetch versions using a more reliable query
$versions = $pdo->prepare("SELECT * FROM app_versions WHERE app_id = ? ORDER BY created_at DESC, id DESC");
$versions->execute([$app_id]);
$version_list = $versions->fetchAll();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-white">Manage APK: <?php echo htmlspecialchars($app_data['app_name'] ?? 'Unknown App'); ?></h1>
    <a href="apps.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if (isset($msg)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo $msg; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-alert="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4 bg-dark border-0">
            <div class="card-header bg-dark border-secondary py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upload New APK</h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="apkUploadForm">
                    <div class="mb-3">
                        <label class="form-label text-white">Display Name</label>
                        <input type="text" name="version_name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Update v1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Select APK File</label>
                        <input type="file" name="apk_file" class="form-control bg-dark text-white border-secondary" accept=".apk" required id="apkFileInput">
                    </div>
                    <div class="progress d-none mb-3" style="height: 25px;" id="apkUploadProgress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <button type="submit" name="add_version" class="btn btn-primary w-100" id="uploadApkBtn">
                        <i class="fas fa-upload me-2"></i> Start Upload
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4 bg-dark border-0">
            <div class="card-header bg-dark border-secondary py-3">
                <h6 class="m-0 font-weight-bold text-primary">Uploaded Files History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="text-white">
                            <tr>
                                <th>Name</th>
                                <th>Upload Date</th>
                                <th>Status</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted">
                            <?php if (empty($version_list)): ?>
                            <tr><td colspan="4" class="text-center py-4">No files uploaded yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach($version_list as $v): ?>
                            <tr>
                                <td class="text-white"><?php echo htmlspecialchars($v['version_name']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($v['created_at'])); ?></td>
                                <td>
                                    <?php if ($v['is_latest']): ?>
                                        <span class="badge bg-success">Live</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Old</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $v['apk_url']; ?>" target="_blank" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-external-link-alt"></i> View
                                    </a>
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

<script>
document.getElementById('apkUploadForm').onsubmit = function(e) {
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
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Uploading...';
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressInner.style.width = percent + '%';
                progressInner.innerText = percent + '%';
            }
        });
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Force refresh to bypass potential cache
                    window.location.href = 'app_details.php?id=<?php echo $app_id; ?>&msg=success&t=' + new Date().getTime();
                } else {
                    alert('Upload failed. Please check file size limits.');
                    location.reload();
                }
            }
        };
        
        xhr.open('POST', window.location.href, true);
        xhr.send(formData);
    }
};
</script>
<?php require_once 'footer.php'; ?>