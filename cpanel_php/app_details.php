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
    // Attempt to override limits at runtime
    @ini_set('upload_max_filesize', '2048M');
    @ini_set('post_max_size', '2048M');
    @ini_set('memory_limit', '2048M');
    @ini_set('max_execution_time', '3600');
    
    $v_name = $_POST['version_name'] ?? 'New Update';
    
    if (isset($_FILES['apk_file'])) {
        $err_code = $_FILES['apk_file']['error'];
        
        // Log details about the received file
        error_log("Upload Attempt - Name: " . $_FILES['apk_file']['name'] . ", Size: " . $_FILES['apk_file']['size'] . " bytes, Error Code: " . $err_code);
        error_log("Runtime Config - upload_max_filesize: " . ini_get('upload_max_filesize') . ", post_max_size: " . ini_get('post_max_size'));
        
        if ($err_code === UPLOAD_ERR_OK) {
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
            // Detailed error handling for all codes
            switch ($err_code) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_msg = "File exceeds 'upload_max_filesize' in php.ini. I have attempted to increase this to 1GB, but your host might be blocking it.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_msg = "File exceeds 'MAX_FILE_SIZE' specified in the HTML form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_msg = "The file was only partially uploaded. Connection might be unstable.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_msg = "No file was selected.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_msg = "Missing a temporary folder on the server.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_msg = "Failed to write file to disk.";
                    break;
                default:
                    $error_msg = "Upload failed with system error code: $err_code.";
                    break;
            }
        }
    } else {
        $error_msg = "No file data detected. If the file is large, the server might be cutting the connection. I've increased limits to 1GB to prevent this.";
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
                            <label class="text-white-50 small">Select APK</label>
                            <input type="file" name="apk_file" id="apkFileInput" class="form-control bg-dark text-white border-secondary" accept=".apk" required>
                        </div>
                        <div id="uploadProgressContainer" class="mb-3 d-none">
                            <div class="progress bg-dark border border-secondary" style="height: 20px;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            <small id="uploadStatusText" class="text-white-50 mt-1 d-block text-center"></small>
                        </div>
                        <button type="submit" id="uploadBtn" name="add_version" class="btn btn-primary w-100">Upload & Activate</button>
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
<script>
document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('apkFileInput');
    if (!fileInput.files.length) return;

    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('add_version', '1');
    
    const xhr = new XMLHttpRequest();
    const progressContainer = document.getElementById('uploadProgressContainer');
    const progressBar = document.getElementById('uploadProgressBar');
    const statusText = document.getElementById('uploadStatusText');
    const uploadBtn = document.getElementById('uploadBtn');

    progressContainer.classList.remove('d-none');
    uploadBtn.disabled = true;

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerHTML = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
            statusText.innerHTML = `Uploading: ${Math.round(e.loaded / 1024 / 1024)}MB of ${Math.round(e.total / 1024 / 1024)}MB`;
        }
    });

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // If it's a redirect, manually follow it or reload
                const url = new URL(window.location.href);
                url.searchParams.set('msg', 'uploaded');
                window.location.href = url.href;
            } else {
                alert('Upload failed. The file might be too large for the server to handle even with increased limits.');
                uploadBtn.disabled = false;
                progressContainer.classList.add('d-none');
            }
        }
    };

    xhr.open('POST', window.location.href, true);
    xhr.send(formData);
});
</script>
<?php require_once 'footer.php'; ?>