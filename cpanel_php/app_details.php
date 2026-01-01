<?php
require_once 'header.php';

$app_id = $_GET['id'] ?? null;
if (!$app_id) header("Location: apps.php");

$app = $pdo->prepare("SELECT * FROM apps WHERE id = ?");
$app->execute([$app_id]);
$app_data = $app->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_version'])) {
    $v_name = $_POST['version_name'];
    $v_code = $_POST['version_code'];
    $apk_url = $_POST['apk_url'];
    
    $pdo->prepare("UPDATE app_versions SET is_latest = FALSE WHERE app_id = ?")->execute([$app_id]);
    $stmt = $pdo->prepare("INSERT INTO app_versions (app_id, version_name, version_code, apk_url, is_latest) VALUES (?, ?, ?, ?, TRUE)");
    $stmt->execute([$app_id, $v_name, $v_code, $apk_url]);
    $msg = "Version added";
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
            <form method="POST">
                <div class="modal-header"><h5>Add Version</h5></div>
                <div class="modal-body">
                    <input type="text" name="version_name" class="form-control mb-2" placeholder="1.0.1" required>
                    <input type="number" name="version_code" class="form-control mb-2" placeholder="2" required>
                    <input type="text" name="apk_url" class="form-control" placeholder="Direct APK URL" required>
                </div>
                <div class="modal-footer"><button type="submit" name="add_version" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>