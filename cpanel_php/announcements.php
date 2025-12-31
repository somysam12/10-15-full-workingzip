<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_announcement'])) {
        $start_time = $_POST['ann_start_date'] . ' ' . $_POST['ann_start_time'];
        $end_time = $_POST['ann_end_date'] . ' ' . $_POST['ann_end_time'];
        
        $pdo->prepare("UPDATE announcements SET active = 0")->execute();
        $stmt = $pdo->prepare("INSERT INTO announcements (message, start_time, end_time, active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$_POST['ann_text'], $start_time, $end_time]);
        $msg = "Announcement successfully published and scheduled for all users!";
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

<?php require_once 'footer.php'; ?>
