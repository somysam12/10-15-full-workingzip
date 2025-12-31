<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_panel'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO panels (name, url, site_key) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['p_name'], $_POST['p_url'], $_POST['p_key']]);
            $msg = "Panel added successfully!";
        } catch (Exception $e) { $error = "Error: " . $e->getMessage(); }
    }
    if (isset($_POST['delete_panel'])) {
        $stmt = $pdo->prepare("DELETE FROM panels WHERE id = ?");
        $stmt->execute([$_POST['panel_id']]);
        $msg = "Panel removed!";
    }
}
$panels = getAllPanels();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Panels</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Add New Panel</h6>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Panel Name</label>
                <input type="text" name="p_name" class="form-control" placeholder="e.g., Global VIP" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Website URL</label>
                <input type="url" name="p_url" class="form-control" placeholder="https://..." required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Unique Key</label>
                <input type="text" name="p_key" class="form-control" placeholder="global_vip" required>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" name="add_panel" class="btn btn-primary"><i class="fas fa-plus"></i> Add Panel</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Panel List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Site Key</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($panels as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><small><?php echo htmlspecialchars($p['url']); ?></small></td>
                        <td><code><?php echo htmlspecialchars($p['site_key']); ?></code></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="panel_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="delete_panel" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
