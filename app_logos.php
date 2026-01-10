<?php
require_once 'config.php';
requireLogin();

$app_id = isset($_GET['app_id']) ? (int)$_GET['app_id'] : 0;
$error_msg = "";
$success_msg = "";

$apps_stmt = $pdo->query("SELECT id, app_name FROM apps ORDER BY app_name ASC");
$all_apps = $apps_stmt->fetchAll();

if (!$app_id && !empty($all_apps)) {
    $app_id = $all_apps[0]['id'];
}

require_once 'header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-white">App Logo Management</h1>
        <a href="apps.php" class="btn btn-outline-light btn-sm">Back to Apps</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark border-secondary shadow">
                <div class="card-header bg-dark border-secondary text-primary font-weight-bold">
                    Select App to Update Logo
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-9">
                                <label class="text-white-50 small mb-2">Select Application</label>
                                <select name="app_id" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                                    <?php foreach ($all_apps as $app): ?>
                                        <option value="<?php echo $app['id']; ?>" <?php echo $app_id == $app['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($app['app_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Load App</button>
                            </div>
                        </div>
                    </form>

                    <?php if ($app_id): 
                        $curr_app_stmt = $pdo->prepare("SELECT app_name, iconUrl FROM apps WHERE id = ?");
                        $curr_app_stmt->execute([$app_id]);
                        $curr_app = $curr_app_stmt->fetch();
                    ?>
                        <hr class="border-secondary my-4">
                        
                        <div class="text-center mb-4">
                            <h4 class="text-white mb-3">Current Logo for: <?php echo htmlspecialchars($curr_app['app_name']); ?></h4>
                            <div class="mb-3">
                                <?php if ($curr_app['iconUrl']): ?>
                                    <img src="<?php echo htmlspecialchars($curr_app['iconUrl']); ?>" class="rounded shadow-lg" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid var(--primary-color);">
                                <?php else: ?>
                                    <div class="rounded bg-dark border border-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                        <i class="fas fa-image fa-4x text-white-50"></i>
                                    </div>
                                    <p class="text-white-50 mt-2">No logo set yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <form method="POST" action="branding.php" enctype="multipart/form-data" class="bg-dark-subtle p-4 rounded border border-secondary">
                            <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">
                            <input type="hidden" name="redirect_to" value="app_logos.php?app_id=<?php echo $app_id; ?>">
                            
                            <div class="mb-3">
                                <label class="text-white-50 small mb-2">Upload New Icon / Logo</label>
                                <input type="file" name="app_icon" class="form-control bg-dark text-white border-secondary" accept="image/*" required>
                                <div class="form-text text-white-50 mt-2">Recommended: Square PNG/JPG (512x512)</div>
                            </div>
                            
                            <button type="submit" name="update_app_icon" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-upload me-2"></i> Update App Logo
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
