<?php
require_once 'header.php';
require_once 'sidebar.php';
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2" style="border-left: .25rem solid var(--primary-color) !important;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Status</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $app_enabled ? 'Active' : 'Maintenance'; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-power-off fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2" style="border-left: .25rem solid var(--success-color) !important;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Panels</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count(getAllPanels()); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Active Announcement</h6>
    </div>
    <div class="card-body">
        <?php if ($ann): ?>
            <p><strong>Message:</strong> <?php echo htmlspecialchars($ann['message']); ?></p>
            <p><small><i class="fas fa-clock"></i> Starts: <?php echo $ann['start_time']; ?></small></p>
            <p><small><i class="fas fa-clock"></i> Ends: <?php echo $ann['end_time']; ?></small></p>
        <?php else: ?>
            <p class="text-muted">No active announcement currently.</p>
        <?php endif; ?>
        <a href="announcements.php" class="btn btn-primary btn-sm mt-2">Manage</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>
