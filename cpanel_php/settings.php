<?php
require_once 'header.php';
// Settings Management
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_config'])) {
        foreach($_POST['config'] as $key => $val) {
            setConfig($key, $val);
        }
        $msg = "Settings updated successfully";
    }
    
    if (isset($_POST['update_admin'])) {
        $new_user = $_POST['admin_user'];
        $new_pass = $_POST['admin_pass'];
        
        if (!empty($new_user) && !empty($new_pass)) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET username = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$new_user, $hash, $_SESSION['admin_id']]);
            $_SESSION['username'] = $new_user;
            $msg = "Admin credentials updated successfully";
        }
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/logo.png');
        $msg = "Logo updated successfully";
    }
}
$configs = getAllConfig();
?>
<h1 class="h3 mb-4">Settings & Configuration</h1>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header">App Configuration</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>App Name</label>
                        <input type="text" name="config[app_title]" class="form-control" value="<?php echo htmlspecialchars($configs['app_title'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label>Maintenance Message</label>
                        <textarea name="config[maintenance_message]" class="form-control"><?php echo htmlspecialchars($configs['maintenance_message'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_config" class="btn btn-primary">Save Configuration</button>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header">Change Admin Credentials</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>New Admin Username</label>
                        <input type="text" name="admin_user" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>New Admin Password</label>
                        <input type="password" name="admin_pass" class="form-control" placeholder="Enter new password" required>
                    </div>
                    <button type="submit" name="update_admin" class="btn btn-warning">Update Credentials</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header">App Logo</div>
            <div class="card-body text-center">
                <img src="logo.png?v=<?php echo time(); ?>" id="logoPreview" class="img-thumbnail mb-3" style="max-height: 150px;">
                <form method="POST" enctype="multipart/form-data" id="logoUploadForm">
                    <input type="file" name="logo" class="form-control mb-3" accept=".png" id="logoFileInput" required>
                    <div class="progress d-none mb-3" id="logoUploadProgress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100" id="logoUploadBtn">Upload Logo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('logoUploadForm').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    const progressBar = document.getElementById('logoUploadProgress');
    const progressInner = progressBar.querySelector('.progress-bar');
    const uploadBtn = document.getElementById('logoUploadBtn');
    
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
};
</script>
<?php require_once 'footer.php'; ?>