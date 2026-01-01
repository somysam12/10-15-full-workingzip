<?php
require_once 'header.php';

// Fetch stats
$total_apps = $pdo->query("SELECT COUNT(*) FROM apps")->fetchColumn();
$total_downloads = $pdo->query("SELECT COUNT(*) FROM download_stats")->fetchColumn();
$total_panels = $pdo->query("SELECT COUNT(*) FROM panels")->fetchColumn();
$app_status = getConfig('app_status', 'ON');
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="appStatusToggle" <?php echo $app_status == 'ON' ? 'checked' : ''; ?>>
        <label class="form-check-label" for="appStatusToggle">App Online Status</label>
    </div>
</div>

<div class="row">
    <!-- Total Apps Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2 border-start border-primary border-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Apps</div>
                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $total_apps; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-mobile-screen fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Downloads Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2 border-start border-success border-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Downloads</div>
                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $total_downloads; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-download fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Panels Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2 border-start border-info border-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Panels</div>
                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $total_panels; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-layer-group fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Active Announcement</h6>
            </div>
            <div class="card-body">
                <?php if ($ann): ?>
                    <h5><?php echo htmlspecialchars($ann['title']); ?></h5>
                    <p><?php echo htmlspecialchars($ann['message']); ?></p>
                    <span class="badge bg-<?php echo $ann['priority'] == 'urgent' ? 'danger' : ($ann['priority'] == 'warning' ? 'warning' : 'info'); ?>">
                        <?php echo ucfirst($ann['priority']); ?>
                    </span>
                <?php else: ?>
                    <p class="text-muted">No active announcement.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('appStatusToggle').addEventListener('change', function() {
    const status = this.checked ? 'ON' : 'OFF';
    fetch('api.php?action=toggle_server', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({enabled: this.checked})
    }).then(res => res.json()).then(data => {
        if(data.success) location.reload();
    });
});
</script>

<?php require_once 'footer.php'; ?>