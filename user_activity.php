<?php
require_once 'config.php';
requireLogin();

// Force India Timezone for MySQL/PHP consistency
date_default_timezone_set('Asia/Kolkata');
$pdo->exec("SET time_zone = '+05:30'");

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
        max-width: 1200px;
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
        font-size: 0.85rem;
        color: #fff; 
        display: block;
        margin-top: 6px;
        padding: 8px 12px;
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.15);
    }
    .active-badge {
        font-size: 0.75rem;
        padding: 3px 10px;
        border-radius: 4px;
        background: rgba(0, 255, 0, 0.3);
        color: #00ff00;
        border: 1px solid rgba(0, 255, 0, 0.5);
        font-weight: bold;
        text-shadow: 0 0 5px rgba(0,255,0,0.5);
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { opacity: 0.7; transform: scale(0.98); }
        50% { opacity: 1; transform: scale(1); }
        100% { opacity: 0.7; transform: scale(0.98); }
    }
    .offline-badge {
        font-size: 0.75rem;
        padding: 3px 10px;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.1);
        color: #ddd;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .usage-badge {
        background: #1a1d21;
        color: #0dcaf0;
        border: 1px solid #0dcaf0;
        padding: 6px 12px;
        border-radius: 6px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 1rem;
        font-weight: bold;
    }
    .time-text {
        color: #00f2fe;
        font-size: 0.85rem;
        font-weight: 500;
        text-shadow: 0 0 2px rgba(0,0,0,0.5);
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
                <div class="table-responsive" style="max-height: 80vh;">
                    <table class="table table-dark table-hover align-middle mb-0 text-center">
                        <thead class="sticky-header">
                            <tr>
                                <th>User Name</th>
                                <th>License Key</th>
                                <th class="d-none d-md-table-cell">Device ID</th>
                                <th>Activity Tracker (India Time)</th>
                                <th>Total Usage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): 
                                $total_mins = floor($u['total_usage_seconds'] / 60);
                                $total_hours = floor($total_mins / 60);
                                $remaining_mins = $total_mins % 60;
                                
                                $timezone = new DateTimeZone('Asia/Kolkata');
                                $last_login = new DateTime($u['last_login_at']);
                                $last_login->setTimezone($timezone);

                                // Fetch recent sessions including active ones
                                try {
                                    $stmt_sess = $pdo->prepare("SELECT login_time, last_heartbeat, session_end, duration_seconds FROM user_sessions WHERE license_key = ? AND device_id = ? ORDER BY id DESC LIMIT 3");
                                    $stmt_sess->execute([$u['license_key'], $u['device_id']]);
                                    $sessions = $stmt_sess->fetchAll();
                                } catch (Exception $e) {
                                    $sessions = [];
                                }
                            ?>
                                <tr style="cursor: pointer;" onclick="showUserStats(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                    <td class="fw-bold text-primary"><?php echo htmlspecialchars($u['user_name']); ?></td>
                                    <td><code class="text-info"><?php echo htmlspecialchars($u['license_key']); ?></code></td>
                                    <td class="d-none d-md-table-cell"><code class="text-info" style="color: #0dcaf0 !important;"><?php echo htmlspecialchars($u['device_id']); ?></code></td>
                                    <td>
                                        <div class="text-start px-2">
                                            <div class="time-text mb-2"><i class="fas fa-clock me-1"></i>Last: <?php echo $last_login->format('d M, H:i'); ?></div>
                                            <?php foreach($sessions as $s): 
                                                $s_start = new DateTime($s['login_time']);
                                                $s_start->setTimezone($timezone);
                                                $s_dur = floor($s['duration_seconds'] / 60);
                                                
                                                // STRICT HEARTBEAT CHECK
                                                $heartbeat_time = strtotime($s['last_heartbeat']);
                                                $server_time = time();
                                                $diff = abs($server_time - $heartbeat_time);
                                                
                                                // If heartbeat is within last 180s AND session is not closed
                                                $is_active = ($s['session_end'] === null && $diff < 180);
                                            ?>
                                                <div class="session-details mb-1 d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <i class="fas fa-history me-1 text-primary"></i><?php echo $s_start->format('H:i'); ?> 
                                                        <span class="ms-2" style="color: #00ff00; font-weight: 600;">(<?php echo $s_dur; ?>m)</span>
                                                    </span>
                                                    <?php if ($is_active): ?>
                                                        <span class="active-badge">ONLINE</span>
                                                    <?php else: ?>
                                                        <span class="offline-badge">OFFLINE</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="usage-badge">
                                            <?php echo ($total_hours > 0 ? $total_hours . "h " : "") . $remaining_mins . "m"; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['is_blocked']): ?>
                                            <span class="status-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">BLOCKED</span>
                                        <?php else: ?>
                                            <span class="status-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25">ACTIVE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" onclick="event.stopPropagation();">
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

<!-- Modal for User Stats -->
<div class="modal fade" id="userStatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalUserName">User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>
</div>

<script>
function showUserStats(user) {
    document.getElementById('modalUserName').innerText = user.user_name + " - Statistics";
    const total_mins = Math.floor(user.total_usage_seconds / 60);
    const total_hours = Math.floor(total_mins / 60);
    const remaining_mins = total_mins % 60;
    
    let body = `
        <div class="mb-3">
            <label class="text-muted d-block small">License Key</label>
            <div class="text-info fw-bold">${user.license_key}</div>
        </div>
        <div class="mb-3">
            <label class="text-muted d-block small">Device ID</label>
            <div class="text-info small">${user.device_id}</div>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="p-3 bg-secondary bg-opacity-10 rounded border border-secondary text-center">
                    <div class="text-muted small">Total Usage</div>
                    <div class="h5 mb-0 text-primary">${total_hours > 0 ? total_hours + 'h ' : ''}${remaining_mins}m</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-secondary bg-opacity-10 rounded border border-secondary text-center">
                    <div class="text-muted small">Status</div>
                    <div class="h5 mb-0 ${user.is_blocked == 1 ? 'text-danger' : 'text-success'}">${user.is_blocked == 1 ? 'BLOCKED' : 'ACTIVE'}</div>
                </div>
            </div>
        </div>
        <div class="mb-0">
            <label class="text-muted d-block small mb-2">Registration Date (India Time)</label>
            <div class="small text-info">${new Date(user.first_login_at).toLocaleString('en-IN', { timeZone: 'Asia/Kolkata' })}</div>
        </div>
    `;
    document.getElementById('modalBody').innerHTML = body;
    new bootstrap.Modal(document.getElementById('userStatsModal')).show();
}
</script>

<?php include 'footer.php'; ?>
