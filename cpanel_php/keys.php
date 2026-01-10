<?php
require_once 'config.php';
requireLogin();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM license_keys WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: keys.php?success=Key deleted");
    exit;
}

// Handle Generate
if (isset($_POST['generate'])) {
    $count = (int)$_POST['count'];
    $duration = (int)$_POST['duration']; // in days
    
    for ($i = 0; $i < $count; $i++) {
        $key = "SHASH-" . strtoupper(bin2hex(random_bytes(4)));
        $expiry = date('Y-m-d H:i:s', strtotime("+$duration days"));
        $stmt = $pdo->prepare("INSERT INTO license_keys (license_key, expires_at) VALUES (?, ?)");
        $stmt->execute([$key, $expiry]);
    }
    header("Location: keys.php?success=$count keys generated");
    exit;
}

$keys = $pdo->query("SELECT * FROM license_keys ORDER BY created_at DESC")->fetchAll();

include 'header.php';
include 'sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card bg-dark text-white border-secondary">
                    <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">License Key Management</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">Generate Keys</button>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Key</th>
                                        <th>Status</th>
                                        <th>Expires At</th>
                                        <th>Device ID</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($keys as $k): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($k['license_key']); ?></code></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($k['status'] == 'active') ? 'success' : 'danger'; ?>">
                                                    <?php echo strtoupper($k['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $k['expires_at']; ?></td>
                                            <td><?php echo $k['device_id'] ?: 'Not Bound'; ?></td>
                                            <td><?php echo $k['created_at']; ?></td>
                                            <td>
                                                <a href="?delete=<?php echo $k['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this key?')">Delete</a>
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
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <form method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Generate New Keys</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Number of Keys</label>
                        <input type="number" name="count" class="form-control bg-secondary text-white border-0" value="1" min="1" max="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (Days)</label>
                        <select name="duration" class="form-select bg-secondary text-white border-0">
                            <option value="1">1 Day</option>
                            <option value="7">7 Days</option>
                            <option value="30">30 Days</option>
                            <option value="365">1 Year</option>
                            <option value="3650">Lifetime</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="generate" class="btn btn-primary">Generate Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
