<?php
require_once 'header.php';

$app_type = $_SESSION['app_type'] ?? 'master';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_announcement'])) {
        $start_time = $_POST['ann_start_date'] . ' ' . $_POST['ann_start_time'];
        $end_time = $_POST['ann_end_date'] . ' ' . $_POST['ann_end_time'];
        $target_app = $_POST['target_app'];
        
        // Deactivate old announcements for the same target
        $pdo->prepare("UPDATE announcements SET active = 0 WHERE app_type = ? OR app_type = 'all'")->execute([$target_app]);
        
        $stmt = $pdo->prepare("INSERT INTO announcements (message, title, button_text, button_link, type, start_time, end_time, app_type, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$_POST['ann_text'], $_POST['ann_title'], $_POST['btn_text'], $_POST['btn_link'], $_POST['ann_type'], $start_time, $end_time, $target_app]);
        $msg = "Announcement successfully published for " . ($target_app === 'all' ? 'All Apps' : ucfirst($target_app) . ' App') . "!";
    }
    if (isset($_POST['delete_announcement'])) {
        $pdo->prepare("UPDATE announcements SET active = 0 WHERE id = ?")->execute([$_POST['ann_id']]);
        $msg = "Announcement deleted successfully!";
    }
}
$ann = getActiveAnnouncement();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Announcements</h1>
</div>

<?php if (isset($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Create Announcement</h6>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Announcement Title</label>
                    <input type="text" name="ann_title" class="form-control" placeholder="e.g. Important Update" value="<?php echo $ann ? htmlspecialchars($ann['title']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Target Application</label>
                    <select name="target_app" class="form-select border-primary">
                        <option value="all" <?php echo ($ann['app_type'] ?? '') == 'all' ? 'selected' : ''; ?>>All Apps (Master & Panel)</option>
                        <option value="master" <?php echo ($ann['app_type'] ?? '') == 'master' ? 'selected' : ($app_type === 'master' ? 'selected' : ''); ?>>Master App Only</option>
                        <option value="panel" <?php echo ($ann['app_type'] ?? '') == 'panel' ? 'selected' : ($app_type === 'panel' ? 'selected' : ''); ?>>Panel App Only</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="ann_text" class="form-control" rows="3" placeholder="Enter message..."><?php echo $ann ? htmlspecialchars($ann['message']) : ''; ?></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="btn_text" class="form-control" placeholder="e.g. Update Now" value="<?php echo $ann ? htmlspecialchars($ann['button_text']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Button Link</label>
                    <input type="url" name="btn_link" class="form-control" placeholder="https://..." value="<?php echo $ann ? htmlspecialchars($ann['button_link']) : ''; ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Display Type</label>
                <select name="ann_type" class="form-select">
                    <option value="banner" <?php echo ($ann['type'] ?? '') == 'banner' ? 'selected' : ''; ?>>Banner (Top of screen)</option>
                    <option value="popup" <?php echo ($ann['type'] ?? '') == 'popup' ? 'selected' : ''; ?>>Popup (Modal dialog)</option>
                </select>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Start Date & Time</label>
                    <div class="input-group">
                        <input type="date" name="ann_start_date" class="form-control" value="<?php echo $ann ? date('Y-m-d', strtotime($ann['start_time'])) : date('Y-m-d'); ?>">
                        <input type="time" name="ann_start_time" class="form-control" value="<?php echo $ann ? date('H:i', strtotime($ann['start_time'])) : date('H:i'); ?>">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fas fa-calendar-check"></i> End Date & Time</label>
                    <div class="input-group">
                        <input type="date" name="ann_end_date" class="form-control" value="<?php echo $ann ? date('Y-m-d', strtotime($ann['end_time'])) : date('Y-m-d', strtotime('+1 day')); ?>">
                        <input type="time" name="ann_end_time" class="form-control" value="<?php echo $ann ? date('H:i', strtotime($ann['end_time'])) : date('H:i'); ?>">
                    </div>
                </div>
            </div>
            
            <button type="submit" name="update_announcement" class="btn btn-primary btn-lg w-100">Publish Announcement</button>
        </form>
    </div>
</div>

<?php if ($ann): ?>
<div class="card shadow mb-4 border-left-danger" style="border-left: .25rem solid #e74c3c !important;">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-danger">Current Active Announcement</h6>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
            <input type="hidden" name="ann_id" value="<?php echo $ann['id']; ?>">
            <button type="submit" name="delete_announcement" class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i> Delete Announcement
            </button>
        </form>
    </div>
    <div class="card-body">
        <p class="mb-0"><?php echo htmlspecialchars($ann['message']); ?></p>
        <div class="mt-2">
            <small class="text-muted"><i class="fas fa-clock"></i> Scheduled: <?php echo $ann['start_time']; ?> to <?php echo $ann['end_time']; ?></small>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
