<?php
require_once 'header.php';

// Handle App creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_app'])) {
    $name = $_POST['app_name'];
    $package = $_POST['package_name'];
    $stmt = $pdo->prepare("INSERT INTO apps (app_name, package_name) VALUES (?, ?)");
    $stmt->execute([$name, $package]);
    $msg = "App added successfully";
}

$apps = $pdo->query("SELECT * FROM apps ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">APK Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppModal">
        <i class="fas fa-plus"></i> Add New App
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>App Name</th>
                        <th>Package Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apps as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($app['app_name']); ?></td>
                        <td><?php echo htmlspecialchars($app['package_name']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $app['is_enabled'] ? 'success' : 'danger'; ?>">
                                <?php echo $app['is_enabled'] ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="app_details.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info text-white">Manage</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add App Modal -->
<div class="modal fade" id="addAppModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New App</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">App Name</label>
                        <input type="text" name="app_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Package Name</label>
                        <input type="text" name="package_name" class="form-control" placeholder="com.example.app" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_app" class="btn btn-primary">Create App</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>