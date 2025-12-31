<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_announcement'])) {
        $pdo->prepare("UPDATE announcements SET active = 0")->execute();
        $stmt = $pdo->prepare("INSERT INTO announcements (message, start_time, end_time, active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$_POST['ann_text'], $_POST['ann_start'], $_POST['ann_end']]);
        $msg = "Announcement updated!";
    }
}
$ann = getActiveAnnouncement();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Announcements</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Create Announcement</h6>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Message</label>
                <textarea name="ann_text" class="form-control" rows="4" placeholder="Enter message to display in app..."><?php echo $ann ? htmlspecialchars($ann['message']) : ''; ?></textarea>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Start Time</label>
                    <input type="datetime-local" name="ann_start" class="datetime-input" value="<?php echo $ann ? date('Y-m-d\TH:i', strtotime($ann['start_time'])) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-calendar-check"></i> End Time</label>
                    <input type="datetime-local" name="ann_end" class="datetime-input" value="<?php echo $ann ? date('Y-m-d\TH:i', strtotime($ann['end_time'])) : ''; ?>">
                </div>
            </div>
            
            <button type="submit" name="update_announcement" class="btn btn-primary btn-lg w-100">Publish Announcement</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
