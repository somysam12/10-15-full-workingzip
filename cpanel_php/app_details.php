<?php
// --- UNIVERSAL AJAX ERROR HANDLER ---
function custom_error_handler($level, $message, $file, $line) {
    if (error_reporting() & $level) {
        throw new ErrorException($message, 0, $level, $file, $line);
    }
}
set_error_handler('custom_error_handler');

function fatal_shutdown_handler() {
    $last_error = error_get_last();
    if ($last_error && in_array($last_error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=UTF-8', true, 500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'A fatal server error occurred.',
                'details' => $last_error['message']
            ]);
        }
    }
}
register_shutdown_function('fatal_shutdown_handler');

// INSTRUCTION FOR USER: PHP Error Code 1 means the file is larger than 'upload_max_filesize' in your cPanel PHP settings.
// This block tries to override it, but cPanel often requires changing it in "Select PHP Version" -> "Options".

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

require_once 'config.php';

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

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $app_name_stmt = $pdo->prepare("SELECT app_name FROM apps WHERE id = ?");
    $app_name_stmt->execute([$app_id]);
    $app_name = $app_name_stmt->fetchColumn() ?: "Unknown App";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete_version'])) {
            $v_id = (int)$_POST['version_id'];
            
            // Get file path before deleting
            $v_stmt = $pdo->prepare("SELECT apk_url FROM app_versions WHERE id = ? AND app_id = ?");
            $v_stmt->execute([$v_id, $app_id]);
            $v_url = $v_stmt->fetchColumn();
            
            if ($v_url) {
                $base_path = dirname($_SERVER['SCRIPT_NAME']);
                if ($base_path === DIRECTORY_SEPARATOR || $base_path === '.') $base_path = '';
                $relative_path = str_replace(request_protocol() . '://' . $_SERVER['HTTP_HOST'] . $base_path . '/', '', $v_url);
                if (file_exists($relative_path)) @unlink($relative_path);
                
                $pdo->prepare("DELETE FROM app_versions WHERE id = ?")->execute([$v_id]);
                $success_msg = "Version deleted successfully!";
            }
        } elseif (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] !== UPLOAD_ERR_OK) {
            $err_code = $_FILES['apk_file']['error'] ?? 'Unknown';
            $err_detail = "";
            switch($err_code) {
                case 1: $err_detail = "The file is too large for the server's PHP configuration (upload_max_filesize). Please increase this in cPanel -> Select PHP Version -> Options."; break;
                case 2: $err_detail = "The file is too large for the HTML form."; break;
                case 3: $err_detail = "The file was only partially uploaded."; break;
                case 4: $err_detail = "No file was uploaded."; break;
                case 6: $err_detail = "Missing a temporary folder on server."; break;
                case 7: $err_detail = "Failed to write file to disk."; break;
                case 8: $err_detail = "A PHP extension stopped the file upload."; break;
                default: $err_detail = "Unknown upload error."; break;
            }
            throw new Exception("Upload Failed: " . $err_detail);
        }

        $upload_dir = 'uploads/apks/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                error_log("CRITICAL: Failed to create directory: " . $upload_dir);
                throw new Exception("Failed to create upload directory. Check permissions (777) on the 'uploads' folder.");
            }
        }
        
        // Final Fix: Instead of move_uploaded_file which is restricted on some hosts, 
        // try copy() which bypasses certain open_basedir restrictions if move fails.
        if (!move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_path)) {
            if (!copy($_FILES['apk_file']['tmp_name'], $target_path)) {
                error_log("CRITICAL: Final move and copy both failed for: " . $_FILES['apk_file']['tmp_name']);
                throw new Exception("Server Permission Error: Failed to save the file. Please contact host to allow writing to: " . realpath($upload_dir));
            }
        }

        $version_name = $_POST['version_name'] ?? 'New Version';
        $file_ext = pathinfo($_FILES['apk_file']['name'], PATHINFO_EXTENSION);
        $clean_name = preg_replace("/[^a-zA-Z0-9_.-]+/", "_", $version_name);
        $file_name = $app_id . "_" . time() . "_" . $clean_name . "." . $file_ext;
        $target_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_path)) {
            // Log move error
            error_log("Failed to move uploaded file: " . $_FILES['apk_file']['tmp_name'] . " to " . $target_path);
            throw new Exception("Failed to move uploaded file. Check folder permissions (777) on uploads/apks/ and ensure the disk is not full.");
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
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    $error_msg = $e->getMessage();
    if ($is_ajax) {
        if (!headers_sent()) { header('Content-Type: application/json; charset=UTF-8', true, 500); }
        echo json_encode(['success' => false, 'error' => $error_msg]);
        exit;
    }
}

if (!$is_ajax) { require_once 'header.php'; }

$history_stmt = $pdo->prepare("SELECT id, version_name, apk_url, is_latest, created_at FROM app_versions WHERE app_id = ? ORDER BY id DESC");
$history_stmt->execute([$app_id]);
$history_items = $history_stmt->fetchAll();

if (isset($_GET['msg']) && $_GET['msg'] === 'uploaded') { $success_msg = "APK uploaded successfully!"; }
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-white">Manage: <?php echo htmlspecialchars($app_name ?? 'App'); ?></h1>
        <a href="apps.php" class="btn btn-outline-light btn-sm">Back to Apps</a>
    </div>

    <div id="alert-container">
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($success_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card bg-dark border-secondary shadow mb-4">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">App Icon / Logo</div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php 
                        $icon_stmt = $pdo->prepare("SELECT iconUrl FROM apps WHERE id = ?");
                        $icon_stmt->execute([$app_id]);
                        $icon_url = $icon_stmt->fetchColumn();
                        if ($icon_url): ?>
                            <img src="<?php echo htmlspecialchars($icon_url); ?>" class="rounded shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 2px solid var(--primary-color);">
                        <?php else: ?>
                            <div class="rounded bg-dark border border-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                                <i class="fas fa-image fa-2x text-white-50"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="branding.php" enctype="multipart/form-data">
                        <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">
                        <div class="mb-3">
                            <input type="file" name="app_icon" class="form-control form-control-sm bg-dark text-white border-secondary" accept="image/*" required>
                        </div>
                        <button type="submit" name="update_app_icon" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Update Icon
                        </button>
                    </form>
                </div>
            </div>

            <div class="card bg-dark border-secondary shadow mb-4">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">New APK Upload</div>
                <div class="card-body">
                    <form id="uploadForm" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="text-white-50 small">Display Name</label>
                            <input type="text" name="version_name" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50 small">Select APK File</label>
                            <input type="file" name="apk_file" class="form-control bg-dark text-white border-secondary" accept=".apk" required>
                        </div>
                        <div id="uploadProgressContainer" class="mb-3 d-none">
                            <div class="progress bg-dark-subtle border border-secondary" style="height: 25px;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold" style="width: 0%;">0%</div>
                            </div>
                            <small id="uploadStatusText" class="text-white-50 mt-1 d-block text-center"></small>
                        </div>
                        <button type="submit" id="uploadBtn" class="btn btn-primary w-100">
                            <i class="fas fa-upload me-2"></i>Upload & Activate
                        </button>
                    </form>
                    <div class="mt-3 small text-white-50">
                        <i class="fas fa-info-circle me-1"></i> <strong>Note:</strong> If you get "PHP Error Code: 1", you must increase <code>upload_max_filesize</code> in your cPanel PHP Options.
                    </div>
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
                                    <th class="border-secondary px-4">Version</th>
                                    <th class="border-secondary">Status</th>
                                    <th class="border-secondary px-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history_items)): ?>
                                    <tr><td colspan="3" class="text-center py-5 text-white-50">No history found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($history_items as $v): ?>
                                        <tr>
                                            <td class="px-4">
                                                <div class="text-white fw-bold"><?php echo htmlspecialchars($v['version_name']); ?></div>
                                                <small class="text-white-50"><?php echo date('M d, Y', strtotime($v['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $v['is_latest'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $v['is_latest'] ? 'LIVE' : 'OLD'; ?>
                                                </span>
                                            </td>
                                            <td class="px-4 text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="<?php echo htmlspecialchars($v['apk_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info">Download</a>
                                                    <form method="POST" onsubmit="return confirm('Delete this version? File will be removed from server.');">
                                                        <input type="hidden" name="version_id" value="<?php echo $v['id']; ?>">
                                                        <button type="submit" name="delete_version" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const alertContainer = document.getElementById('alert-container');
    const uploadBtn = document.getElementById('uploadBtn');
    const progressContainer = document.getElementById('uploadProgressContainer');
    const progressBar = document.getElementById('uploadProgressBar');
    const statusText = document.getElementById('uploadStatusText');

    alertContainer.innerHTML = '';
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();

    uploadBtn.disabled = true;
    progressContainer.classList.remove('d-none');

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';
            statusText.textContent = `Uploading: ${Math.round(e.loaded / 1024 / 1024)}MB / ${Math.round(e.total / 1024 / 1024)}MB`;
        }
    });

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            console.log("Upload Response:", xhr.responseText); // Debug
            if (xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) { window.location.href = 'app_details.php?id=<?php echo $app_id; ?>&msg=uploaded'; }
                    else { throw new Error(res.error); }
                } catch (err) {
                    alertContainer.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
                    uploadBtn.disabled = false;
                }
            } else {
                let msg = "Server Error";
                try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
                alertContainer.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
                uploadBtn.disabled = false;
            }
        }
    };
    xhr.open('POST', window.location.href, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});
</script>
<?php if (!$is_ajax) require_once 'footer.php'; ?>