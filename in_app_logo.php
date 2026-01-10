<?php
require_once 'header.php';
require_once 'config.php';

$current_type = $_SESSION['app_type'] ?? 'master';
$current_key = ($current_type === 'panel') ? 'panel_logo_url' : 'main_logo_url';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_logo'])) {
        $logo_url = $_POST['logo_url'];
        
        // Handle File Upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === 0) {
            $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            $filename = $_FILES['logo_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newName = "logo_" . $current_type . "_" . time() . "." . $ext;
                $uploadDir = __DIR__ . "/uploads/logos/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $uploadPath = $uploadDir . $newName;
                
                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadPath)) {
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $logo_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/uploads/logos/" . $newName;
                }
            }
        }
        
        if (!empty($logo_url)) {
            setConfig($current_key, $logo_url);
            $msg = "In-App Logo updated successfully!";
        }
    }
    
    if (isset($_POST['delete_logo'])) {
        $current_logo = getConfig($current_key, '');
        if (!empty($current_logo)) {
            // Check if it's a local file and delete it
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
            if (strpos($current_logo, $base_url) === 0) {
                $relative_path = str_replace($base_url, '', $current_logo);
                $full_path = __DIR__ . $relative_path;
                if (file_exists($full_path)) {
                    unlink($full_path);
                }
            }
            setConfig($current_key, '');
            $msg = "Logo deleted successfully!";
        }
    }
}

$current_logo = getConfig($current_key, '');
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">In-App Logo Management</h1>
    <?php if (isset($msg)): ?>
        <div class="alert alert-success py-2 mb-0"><?php echo $msg; ?></div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Update Splash/App Logo (<?php echo ucfirst($current_type); ?>)</h6>
                <?php if ($current_logo): ?>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this logo?');">
                    <button type="submit" name="delete_logo" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i> Delete Current Logo
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Upload Logo File</label>
                        <input type="file" name="logo_file" class="form-control" accept="image/*">
                        <div class="form-text">OR provide a direct image URL below.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Logo URL</label>
                        <input type="url" name="logo_url" class="form-control" value="<?php echo htmlspecialchars($current_logo); ?>" placeholder="https://example.com/logo.png">
                    </div>
                    
                    <button type="submit" name="update_logo" class="btn btn-primary">Update Logo</button>
                </form>
                
                <?php if ($current_logo): ?>
                <div class="mt-4">
                    <h6>Current Preview:</h6>
                    <div class="p-3 border rounded text-center" style="background: rgba(255,255,255,0.05);">
                        <img src="<?php echo htmlspecialchars($current_logo); ?>" alt="Logo Preview" style="max-height: 150px; max-width: 100%;">
                        <div class="mt-2 small text-muted text-break"><?php echo htmlspecialchars($current_logo); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>