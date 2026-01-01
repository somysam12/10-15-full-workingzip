<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_app'])) {
    $name = $_POST['app_name'];
    $stmt = $pdo->prepare("INSERT INTO apps (app_name) VALUES (?)");
    $stmt->execute([$name]);
    $msg = "App folder created!";
}

$apps = $pdo->query("SELECT * FROM apps ORDER BY id DESC")->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-white">APK Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppModal">
        <i class="fas fa-plus"></i> Create New App Folder
    </button>
</div>

<div class="row">
    <?php foreach ($apps as $app): ?>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="h5 mb-1 font-weight-bold text-white"><?php echo htmlspecialchars($app['app_name']); ?></div>
                        <div class="text-xs text-muted mb-2">ID: #<?php echo $app['id']; ?></div>
                        <a href="app_details.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info text-white w-100">
                            <i class="fas fa-folder-open me-1"></i> Open & Upload APK
                        </a>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
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