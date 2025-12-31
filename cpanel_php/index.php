<?php
require_once 'config.php';

// Handle Actions
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update General Settings
    if (isset($_POST['update_settings'])) {
        setConfig('app_enabled', isset($_POST['app_enabled']) ? 'true' : 'false');
        
        // Update Announcement
        $pdo->prepare("UPDATE announcements SET active = 0")->execute();
        $stmt = $pdo->prepare("INSERT INTO announcements (message, start_time, end_time, active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$_POST['ann_text'], $_POST['ann_start'], $_POST['ann_end']]);
        $msg = "Settings and announcement updated!";
    }

    // Add Panel
    if (isset($_POST['add_panel'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO panels (name, url, site_key) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['p_name'], $_POST['p_url'], $_POST['p_key']]);
            $msg = "Panel added successfully!";
        } catch (Exception $e) { $error = "Error adding panel: " . $e->getMessage(); }
    }

    // Delete Panel
    if (isset($_POST['delete_panel'])) {
        $stmt = $pdo->prepare("DELETE FROM panels WHERE id = ?");
        $stmt->execute([$_POST['panel_id']]);
        $msg = "Panel deleted!";
    }

    // Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/logo.png');
        $msg = "Logo updated!";
    }
}

$ann = getActiveAnnouncement();
$panels = getAllPanels();
$app_enabled = getConfig('app_enabled', 'true') === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Silent Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fe; }
        .card { border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="p-4">
<div class="container">
    <h2 class="mb-4 text-primary">🎮 Silent Panel Control Center</h2>

    <?php if ($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <div class="row g-4">
        <!-- Settings -->
        <div class="col-md-7">
            <div class="card p-4">
                <h5>General & Announcement</h5>
                <form method="POST">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="app_enabled" <?php echo $app_enabled ? 'checked' : ''; ?>>
                        <label>App Active</label>
                    </div>
                    <div class="mb-3">
                        <label>Announcement</label>
                        <textarea name="ann_text" class="form-control"><?php echo $ann ? htmlspecialchars($ann['message']) : ''; ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col"><input type="datetime-local" name="ann_start" class="form-control" value="<?php echo $ann ? date('Y-m-d\TH:i', strtotime($ann['start_time'])) : ''; ?>"></div>
                        <div class="col"><input type="datetime-local" name="ann_end" class="form-control" value="<?php echo $ann ? date('Y-m-d\TH:i', strtotime($ann['end_time'])) : ''; ?>"></div>
                    </div>
                    <button type="submit" name="update_settings" class="btn btn-primary mt-3 w-100">Update Control</button>
                </form>
            </div>

            <div class="card p-4 mt-4">
                <h5>Manage Panels</h5>
                <form method="POST" class="row g-2 mb-4">
                    <div class="col-md-4"><input type="text" name="p_name" placeholder="Name" class="form-control" required></div>
                    <div class="col-md-4"><input type="text" name="p_url" placeholder="URL" class="form-control" required></div>
                    <div class="col-md-2"><input type="text" name="p_key" placeholder="Key" class="form-control" required></div>
                    <div class="col-md-2"><button type="submit" name="add_panel" class="btn btn-success w-100">+</button></div>
                </form>
                
                <table class="table align-middle">
                    <?php foreach ($panels as $p): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($p['name']); ?></strong><br><small><?php echo htmlspecialchars($p['url']); ?></small></td>
                        <td><code><?php echo htmlspecialchars($p['site_key']); ?></code></td>
                        <td class="text-end">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="panel_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="delete_panel" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- Logo -->
        <div class="col-md-5">
            <div class="card p-4 text-center">
                <h5>App Logo</h5>
                <img src="logo.png?v=<?php echo time(); ?>" class="img-fluid border mb-3" style="max-height: 150px;">
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="logo" class="form-control mb-2" accept=".png">
                    <button type="submit" class="btn btn-outline-primary w-100">Upload PNG</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>