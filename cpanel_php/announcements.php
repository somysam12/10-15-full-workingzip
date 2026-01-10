<?php
require_once 'header.php';
// Announcements Management
$current_app_type = $_SESSION['app_type'] ?? 'master';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_ann'])) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, active, app_type) VALUES (?, ?, 1, ?)");
        $stmt->execute([$_POST['title'], $_POST['message'], $current_app_type]);
        $msg = "Announcement added successfully";
    }
    if (isset($_POST['delete_ann'])) {
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$_POST['ann_id']]);
        $msg = "Announcement deleted";
    }
}

// Fetch only relevant announcements
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE app_type = ? OR app_type = 'all' ORDER BY id DESC");
$stmt->execute([$current_app_type]);
$anns = $stmt->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?php echo ucfirst($current_app_type); ?> Announcements</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnModal">
        <i class="fas fa-plus me-2"></i> New Announcement
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Target</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anns as $ann): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ann['title']); ?></td>
                        <td><?php echo htmlspecialchars($ann['message']); ?></td>
                        <td><?php echo $ann['app_type']; ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete?');">
                                <input type="hidden" name="ann_id" value="<?php echo $ann['id']; ?>">
                                <button type="submit" name="delete_ann" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addAnnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title">New Announcement</h5></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_ann" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>