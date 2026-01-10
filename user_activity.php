<?php
require_once 'config.php';
requireLogin();

// Handle Actions
if (isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'block') {
        $stmt = $pdo->prepare("UPDATE app_users SET is_blocked = 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        $user = $pdo->query("SELECT license_key FROM app_users WHERE id = $id")->fetch();
        if ($user) {
            $stmt = $pdo->prepare("UPDATE license_keys SET status = 'banned' WHERE license_key = ?");
            $stmt->execute([$user['license_key']]);
        }
        header("Location: user_activity.php?success=User blocked");
        exit;
    } elseif ($_GET['action'] === 'unblock') {
        $stmt = $pdo->prepare("UPDATE app_users SET is_blocked = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        $user = $pdo->query("SELECT license_key FROM app_users WHERE id = $id")->fetch();
        if ($user) {
            $stmt = $pdo->prepare("UPDATE license_keys SET status = 'active' WHERE license_key = ?");
            $stmt->execute([$user['license_key']]);
        }
        header("Location: user_activity.php?success=User unblocked");
        exit;
    }
}

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM app_users";
if ($search) {
    $query .= " WHERE user_name LIKE ? OR license_key LIKE ? OR device_id LIKE ?";
    $stmt = $pdo->prepare($query . " ORDER BY last_login_at DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query($query . " ORDER BY last_login_at DESC");
}
$users = $stmt->fetchAll();

include 'header.php';
include 'sidebar.php';
?>

<style>
    .user-card {
        max-width: 1100px;
        margin: 0 auto;
        border-radius: 15px;
        overflow: hidden;
    }
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: var(--primary-color) #1a1d21;
    }
    .status-pill {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .session-details {
        font-size: 0.7rem;
        color: #6c757d;
        display: block;
        margin-top: 2px;
    }
    .session-details {
        font-size: 0.7rem;
        color: #6c757d;
        display: block;
        margin-top: 2px;
    }
</style>

<div class="main-content p-3 p-md-4">
    <div class="container-fluid">
        <div class="card bg-dark text-white border-secondary shadow user-card">
            <div class="card-header border-secondary d-flex flex-wrap justify-content-between align-items-center py-3 gap-3">
                <h4 class="mb-0"><i class="fas fa-users-cog me-2 text-primary"></i>User Activity</h4>
                <form class="d-flex w-100 w-md-auto" style="max-width: 400px;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control bg-secondary text-white border-0" placeholder="Search name, key or device..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 70vh;">
                    <table class="table table-dark table-hover align-middle mb-0 text-center">
                        <thead class="sticky-header">
                            <tr>
                                <th>User Name</th>
                                <th>License Key</th>
                                <th class="d-none d-md-table-cell">Device ID</th>
                                <th>Last Login</th>
                                <th>Total Usage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): 
                                $hours = floor($u['total_usage_seconds'] / 3600);
                                $mins = floor(($u['total_usage_seconds'] % 3600) / 60);
                                
                                $timezone = new DateTimeZone('Asia/Kolkata');
                                $last_login = new DateTime($u['last_login_at']);
                                $last_login->setTimezone($timezone);

                                // Fetch recent sessions
                                try {
                                    $stmt_sess = $pdo->prepare("SELECT login_time, duration_seconds FROM user_sessions WHERE license_key = ? AND device_id = ? ORDER BY id DESC LIMIT 3");
                                    $stmt_sess->execute([$u['license_key'], $u['device_id']]);
                                    $sessions = $stmt_sess->fetchAll();
                                } catch (Exception $e) {
                                    $sessions = [];
                                }
                            ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($u['user_name']); ?></td>
                                    <td><code class="text-info"><?php echo htmlspecialchars($u['license_key']); ?></code></td>
                                    <td class="d-none d-md-table-cell"><code class="text-info" style="color: #0dcaf0 !important;"><?php echo htmlspecialchars($u['device_id']); ?></code></td>
                                    <td>
                                        <small><?php echo $last_login->format('M d, H:i'); ?></small>
                                        <?php foreach($sessions as $s): 
                                            $s_start = new DateTime($s['login_time']);
                                            $s_start->setTimezone($timezone);
                                            $s_dur = floor($s['duration_seconds'] / 60);
                                        ?>
                                            <span class="session-details">
                                                <i class="fas fa-history me-1"></i><?php echo $s_start->format('H:i'); ?> (<?php echo $s_dur; ?>m)
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo sprintf("%02d:%02d", $hours, $mins); ?></span></td>
                                    <td>
                                        <?php if ($u['is_blocked']): ?>
                                            <span class="status-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">BLOCKED</span>
                                        <?php else: ?>
                                            <span class="status-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25">ACTIVE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($u['is_blocked']): ?>
                                                <a href="?action=unblock&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-unlock"></i></a>
                                            <?php else: ?>
                                                <a href="?action=block&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Block this user?')"><i class="fas fa-ban"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="py-4 text-muted">No user activity found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
