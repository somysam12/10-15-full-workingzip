<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_app'])) {
        $name = $_POST['app_name'];
        $stmt = $pdo->prepare("INSERT INTO apps (app_name) VALUES (?)");
        $stmt->execute([$name]);
        $success_msg = "App folder created!";
    } elseif (isset($_POST['delete_app'])) {
        $del_id = (int)$_POST['app_id'];
        
        // 1. Get all versions to delete files
        $v_stmt = $pdo->prepare("SELECT apk_url FROM app_versions WHERE app_id = ?");
        $v_stmt->execute([$del_id]);
        $versions = $v_stmt->fetchAll();
        
        foreach ($versions as $v) {
            $path = str_replace(request_protocol() . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/', '', $v['apk_url']);
            if (file_exists($path)) @unlink($path);
        }
        
        // 2. Delete from DB (cascading)
        $pdo->prepare("DELETE FROM app_versions WHERE app_id = ?")->execute([$del_id]);
        $pdo->prepare("DELETE FROM apps WHERE id = ?")->execute([$del_id]);
        $success_msg = "App and all versions deleted!";
    }
}

$apps = $pdo->query("SELECT * FROM apps ORDER BY id DESC")->fetchAll();

function request_protocol() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http";
}
?>

<?php if (isset($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-white">APK Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppModal">
        <i class="fas fa-plus"></i> Create New App Folder
    </button>
</div>

<div class="row">
    <?php foreach ($apps as $app): ?>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2 bg-dark">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="h5 mb-1 font-weight-bold text-white"><?php echo htmlspecialchars($app['app_name']); ?></div>
                        <div class="text-xs text-muted mb-3">ID: #<?php echo $app['id']; ?></div>
                        
                        <div class="d-flex gap-2">
                            <a href="app_details.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info text-white flex-grow-1">
                                <i class="fas fa-folder-open me-1"></i> Open
                            </a>
                            <form method="POST" onsubmit="return confirm('Delete this app and ALL its APK versions? This cannot be undone.');">
                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                <button type="submit" name="delete_app" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add App Modal -->
<div class="modal fade" id="addAppModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <form method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Create App Folder</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">App Name</label>
                        <input type="text" name="app_name" class="form-control bg-secondary text-white border-0" placeholder="e.g. My Awesome App" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="add_app" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>