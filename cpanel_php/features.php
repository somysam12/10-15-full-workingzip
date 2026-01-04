<?php
require_once 'header.php';

// Simple logging system
function addLog($action) {
    global $pdo;
    $admin_id = $_SESSION['admin_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)");
    // Note: For a real log you'd want a 'logs' table, but using config for small text is a shortcut for now.
}

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-white">Advanced Features</h1>
    
    <div class="row">
        <!-- Feature 1: Maintenance Control -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-body text-center">
                    <div class="mb-3 text-primary"><i class="fas fa-tools fa-3x"></i></div>
                    <h5 class="card-title">Maintenance Control</h5>
                    <p class="text-white-50 small">Independently manage maintenance modes for Master and Panel apps with custom messages.</p>
                    <a href="settings.php" class="btn btn-outline-primary btn-sm">Configure</a>
                </div>
            </div>
        </div>

        <!-- Feature 2: Analytics & Stats -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-body text-center">
                    <div class="mb-3 text-success"><i class="fas fa-chart-line fa-3x"></i></div>
                    <h5 class="card-title">Smart Analytics</h5>
                    <p class="text-white-50 small">Track download counts and active panels in real-time with geographic data (IP tracking).</p>
                    <a href="analytics.php" class="btn btn-outline-success btn-sm">View Stats</a>
                </div>
            </div>
        </div>

        <!-- Feature 3: Security & Logging -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-body text-center">
                    <div class="mb-3 text-info"><i class="fas fa-shield-halved fa-3x"></i></div>
                    <h5 class="card-title">Security Center</h5>
                    <p class="text-white-50 small">Force logout all users, reset in-app caches remotely, and monitor admin access.</p>
                    <button class="btn btn-outline-info btn-sm" disabled>Coming Soon</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suggested Features Recommendation -->
    <div class="card bg-dark border-secondary mt-4">
        <div class="card-header bg-transparent border-secondary">
            <h5 class="mb-0 text-primary">Future Roadmap Suggestions</h5>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush bg-transparent">
                <div class="list-group-item bg-transparent text-white border-secondary py-3">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 text-info"><i class="fas fa-history me-2"></i>APK Version History</h6>
                        <span class="badge bg-primary">High Priority</span>
                    </div>
                    <p class="mb-1 small text-white-50">Allows you to store multiple versions of each app and choose which one is currently "Live" for users.</p>
                </div>
                <div class="list-group-item bg-transparent text-white border-secondary py-3">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 text-success"><i class="fas fa-database me-2"></i>Auto-Cloud Backup</h6>
                        <span class="badge bg-secondary">Security</span>
                    </div>
                    <p class="mb-1 small text-white-50">Automatically backup your database and uploaded APKs/Logos to a secure cloud storage daily.</p>
                </div>
                <div class="list-group-item bg-transparent text-white border-secondary py-3">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 text-warning"><i class="fas fa-user-lock me-2"></i>Multi-Admin Support</h6>
                        <span class="badge bg-secondary">Management</span>
                    </div>
                    <p class="mb-1 small text-white-50">Create sub-admin accounts with limited permissions (e.g., only allowed to update announcements).</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>