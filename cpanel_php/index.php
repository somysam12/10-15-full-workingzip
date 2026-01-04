<?php
require_once 'header.php';

$app_type = $_SESSION['app_type'] ?? 'master';

// Fetch stats
$app_type = $_SESSION['app_type'] ?? 'master';
$total_apps = $pdo->query("SELECT COUNT(*) FROM apps")->fetchColumn();
$total_downloads = $pdo->query("SELECT COUNT(*) FROM download_stats")->fetchColumn();
$total_panels = $pdo->query("SELECT COUNT(*) FROM panels")->fetchColumn();

$status_key = ($app_type === 'panel') ? 'panel_app_status' : 'app_status';
$app_status = getConfig($status_key, 'ON');
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?php echo ucfirst($app_type); ?> Dashboard</h1>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="appStatusToggle" <?php echo $app_status == 'ON' ? 'checked' : ''; ?>>
        <label class="form-check-label" for="appStatusToggle">App Online Status</label>
    </div>
</div>

<div class="row">
    <?php if ($app_type === 'master'): ?>
    <!-- Total Apps Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100 py-2 border-0">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Apps</div>
                        <div class="h3 mb-0 font-weight-bold text-white"><?php echo $total_apps; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-primary-subtle p-3 rounded-circle" style="background: rgba(168, 85, 247, 0.1);">
                            <i class="fas fa-mobile-screen fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Downloads Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100 py-2 border-0">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Downloads</div>
                        <div class="h3 mb-0 font-weight-bold text-white"><?php echo $total_downloads; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-success-subtle p-3 rounded-circle" style="background: rgba(16, 185, 129, 0.1);">
                            <i class="fas fa-download fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($app_type === 'panel'): ?>
    <!-- Active Panels Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100 py-2 border-0">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Panels</div>
                        <div class="h3 mb-0 font-weight-bold text-white"><?php echo $total_panels; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-info-subtle p-3 rounded-circle" style="background: rgba(13, 202, 240, 0.1);">
                            <i class="fas fa-layer-group fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-transparent border-bottom border-secondary">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bullhorn me-2"></i>Active Announcement</h6>
                </div>
                <div class="card-body">
                    <?php if ($ann): ?>
                        <div class="p-4 rounded" style="background: rgba(168, 85, 247, 0.05); border: 1px dashed rgba(168, 85, 247, 0.2);">
                            <h4 class="text-white mb-2"><?php echo htmlspecialchars($ann['title']); ?></h4>
                            <p class="text-main mb-0 fs-5"><?php echo htmlspecialchars($ann['message']); ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No active announcement for <?php echo ucfirst($app_type); ?> app.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Smart Analytics: Live Activity Feed -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-satellite-dish me-2"></i>Live Activity Feed (Smart Analytics)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover small">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $activities = $pdo->query("
                                        (SELECT 'Download' as type, ip_address, downloaded_at as time FROM download_stats)
                                        UNION ALL
                                        (SELECT action as type, ip_address, created_at as time FROM security_logs)
                                        ORDER BY time DESC LIMIT 10
                                    ")->fetchAll();
                                    foreach($activities as $act): ?>
                                        <tr>
                                            <td><span class="badge <?php echo $act['type'] == 'Download' ? 'bg-success' : ($act['type'] == 'UPDATE_SETTINGS' ? 'bg-info' : 'bg-warning'); ?>"><?php echo $act['type']; ?></span></td>
                                            <td><?php echo htmlspecialchars($act['ip_address'] ?? 'Unknown'); ?></td>
                                            <td><?php echo date('M d, H:i', strtotime($act['time'])); ?></td>
                                        </tr>
                                    <?php endforeach;
                                } catch (Exception $e) {
                                    echo "<tr><td colspan='3' class='text-center text-muted'>Activity data unavailable</td></tr>";
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('appStatusToggle').addEventListener('change', function() {
    const status = this.checked ? 'OFF' : 'ON';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'settings.php';
    const fields = {
        'app_status': status,
        'maintenance_msg': 'System is under maintenance.',
        'update_settings': '1'
    };
    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
});
</script>

<?php require_once 'footer.php'; ?>
