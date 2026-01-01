<?php
// --- UNIVERSAL AJAX ERROR HANDLER ---
// This block will catch ANY error and ensure a JSON response is sent for AJAX requests.
function custom_error_handler($level, $message, $file, $line) {
    if (error_reporting() & $level) {
        throw new ErrorException($message, 0, $level, $file, $line);
    }
}
set_error_handler('custom_error_handler');

function fatal_shutdown_handler() {
    $last_error = error_get_last();
    if ($last_error && in_array($last_error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE])) {
        // Check if this was an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=UTF-8', true, 500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'A fatal server error occurred. Check server logs for details.',
                'details' => $last_error['message'] // For debugging
            ]);
        }
    }
}
register_shutdown_function('fatal_shutdown_handler');
// --- END ERROR HANDLER BLOCK ---

// IMPORTANT: Do NOT include header.php before checking if it's an AJAX request
// because header.php usually contains HTML which will break JSON responses.
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

require_once 'config.php';

// --- INITIALIZATION ---
$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$app_id) {
    if ($is_ajax) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['success' => false, 'error' => 'Missing App ID']);
        exit;
    }
    header("Location: apps.php");
    exit;
}

$error_msg = "";
$success_msg = "";

// --- MAIN LOGIC WRAPPED IN TRY/CATCH ---
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $app_name_stmt = $pdo->prepare("SELECT app_name FROM apps WHERE id = ?");
    $app_name_stmt->execute([$app_id]);
    $app_name = $app_name_stmt->fetchColumn() ?: "Unknown App";

    // --- UPLOAD HANDLING ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] !== UPLOAD_ERR_OK) {
            $err_code = $_FILES['apk_file']['error'] ?? 'Unknown';
            throw new Exception("File upload failed. PHP Error Code: " . $err_code);
        }

        $upload_dir = 'uploads/apks/';
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
            throw new Exception("Failed to create upload directory. Check permissions.");
        }

        $version_name = $_POST['version_name'] ?? 'New Version';
        $file_ext = pathinfo($_FILES['apk_file']['name'], PATHINFO_EXTENSION);
        $clean_name = preg_replace("/[^a-zA-Z0-9_.-]+/", "_", $version_name);
        $file_name = $app_id . "_" . time() . "_" . $clean_name . "." . $file_ext;
        $target_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_path)) {
            throw new Exception("Failed to move uploaded file. Check server write permissions for the directory.");
        }
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script_path = $_SERVER['SCRIPT_NAME'];
        $current_dir = dirname($script_path);
        if ($current_dir === DIRECTORY_SEPARATOR || $current_dir === '.') $current_dir = '';
        
        $apk_url = $protocol . "://" . $host . $current_dir . '/' . $target_path;

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE app_versions SET is_latest = 0 WHERE app_id = ?")->execute([$app_id]);
        $pdo->prepare("INSERT INTO app_versions (app_id, version_name, apk_url, is_latest, version_code, created_at) VALUES (?, ?, ?, 1, ?, NOW())")
            ->execute([$app_id, $version_name, $apk_url, time()]);
        $pdo->commit();

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header("Location: app_details.php?id=$app_id&msg=uploaded");
        exit;
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Caught Exception: " . $e->getMessage());
    $error_msg = $e->getMessage();

    if ($is_ajax) {
        if (!headers_sent()) {
             header('Content-Type: application/json; charset=UTF-8', true, 500);
        }
        echo json_encode(['success' => false, 'error' => $error_msg]);
        exit;
    }
}

// Only include header if NOT an AJAX request
if (!$is_ajax) {
    require_once 'header.php';
}

// --- DATA FETCHING FOR DISPLAY ---
$history_stmt = $pdo->prepare("SELECT id, version_name, apk_url, is_latest, created_at FROM app_versions WHERE app_id = ? ORDER BY id DESC");
$history_stmt->execute([$app_id]);
$history_items = $history_stmt->fetchAll();

if (isset($_GET['msg']) && $_GET['msg'] === 'uploaded') {
    $success_msg = "APK uploaded successfully and is now LIVE!";
}
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-white">Manage: <?php echo htmlspecialchars($app_name ?? 'App'); ?></h1>
        <a href="apps.php" class="btn btn-outline-light btn-sm">Back to Apps</a>
    </div>

    <div id="alert-container">
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card bg-dark border-secondary shadow mb-4">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">New APK Upload</div>
                <div class="card-body">
                    <form id="uploadForm" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="version_name" class="text-white-50 small">Display Name</label>
                            <input type="text" id="version_name" name="version_name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. App v2.5 (Hotfix)" required>
                        </div>
                        <div class="mb-3">
                            <label for="apkFileInput" class="text-white-50 small">Select APK File</label>
                            <input type="file" name="apk_file" id="apkFileInput" class="form-control bg-dark text-white border-secondary" accept=".apk" required>
                        </div>
                        <div id="uploadProgressContainer" class="mb-3 d-none">
                            <div class="progress bg-dark-subtle border border-secondary" style="height: 25px;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold" role="progressbar" style="width: 0%;" aria-valuenow="0">0%</div>
                            </div>
                            <small id="uploadStatusText" class="text-white-50 mt-1 d-block text-center"></small>
                        </div>
                        <button type="submit" id="uploadBtn" class="btn btn-primary w-100">
                            <i class="fas fa-upload me-2"></i>Upload & Activate
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card bg-dark border-secondary shadow">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">
                    Uploaded Files History (<?php echo count($history_items); ?>)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead class="small text-uppercase text-white-50">
                                <tr>
                                    <th class="border-secondary px-4">Version Details</th>
                                    <th class="border-secondary">Status</th>
                                    <th class="border-secondary px-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history_items)): ?>
                                    <tr><td colspan="3" class="text-center py-5 text-white-50">No uploads found for this app.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($history_items as $v): ?>
                                        <tr>
                                            <td class="px-4 align-middle">
                                                <div class="text-white fw-bold"><?php echo htmlspecialchars($v['version_name']); ?></div>
                                                <small class="text-white-50"><?php echo date('M d, Y, h:i A', strtotime($v['created_at'])); ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($v['is_latest']): ?>
                                                    <span class="badge bg-success fs-6">LIVE</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary opacity-50">OLD</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 text-end align-middle">
                                                <a href="<?php echo htmlspecialchars($v['apk_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                   <i class="fas fa-download me-1"></i> Download
                                                </a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const progressContainer = document.getElementById('uploadProgressContainer');
    const progressBar = document.getElementById('uploadProgressBar');
    const alertContainer = document.getElementById('alert-container');
    const statusText = document.getElementById('uploadStatusText');

    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        alertContainer.innerHTML = '';
        
        const formData = new FormData(uploadForm);
        const xhr = new XMLHttpRequest();

        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';
        progressContainer.classList.remove('d-none');

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressBar.textContent = percent + '%';
                progressBar.setAttribute('aria-valuenow', percent);
                if (statusText) statusText.textContent = `Uploading: ${Math.round(e.loaded / 1024 / 1024)}MB of ${Math.round(e.total / 1024 / 1024)}MB`;
            }
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    try {
                        const result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            window.location.href = 'app_details.php?id=<?php echo $app_id; ?>&msg=uploaded';
                        } else {
                            throw new Error(result.error || 'Unknown error');
                        }
                    } catch (e) {
                        alertContainer.innerHTML = `<div class="alert alert-danger">Upload Failed: ${e.message}</div>`;
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload & Activate';
                    }
                } else {
                    let errorMsg = 'Server Error';
                    try {
                        const result = JSON.parse(xhr.responseText);
                        errorMsg = result.error || result.details || errorMsg;
                    } catch(e) {}
                    alertContainer.innerHTML = `<div class="alert alert-danger">Upload Failed: ${errorMsg}</div>`;
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload & Activate';
                }
            }
        };

        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });
});
</script>
<?php if (!$is_ajax) require_once 'footer.php'; ?>