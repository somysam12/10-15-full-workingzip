<?php
require_once 'config.php';
requireLogin();

// Handle Actions
if (isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'block') {
        $stmt = $pdo->prepare("UPDATE app_users SET is_blocked = 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        // Also block the associated key
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
        
        // Also unblock the associated key
        $user = $pdo->query("SELECT license_key FROM app_users WHERE id = $id")->fetch();
        if ($user) {
            $stmt = $pdo->prepare("UPDATE license_keys SET status = 'active' WHERE license_key = ?");
            $stmt->execute([$user['license_key']]);
        }
        header("Location: user_activity.php?success=User unblocked");
        exit;
    }
}

try {
    $search = $_GET['search'] ?? '';
    $query = "SELECT * FROM app_users";
    if ($search) {
        $query .= " WHERE user_name LIKE ? OR license_key LIKE ?";
        $stmt = $pdo->prepare($query . " ORDER BY last_login_at DESC");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query($query . " ORDER BY last_login_at DESC");
    }
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $error = "Database Error: " . $e->getMessage();
}

include 'header.php';
include 'sidebar.php';
?>

<div class="main-content p-4">
    <div class="container-fluid">
        <div class="card bg-dark text-white border-secondary shadow">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0"><i class="fas fa-users-cog me-2"></i>User Activity</h4>
                <form class="d-flex" style="max-width: 300px;">
                    <input type="text" name="search" class="form-control bg-secondary text-white border-0 me-2" placeholder="Search user or key..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>License Key</th>
                                <th>Device ID</th>
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
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['user_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($u['license_key']); ?></code></td>
                                    <td><small class="text-muted"><?php echo substr($u['device_id'], 0, 12); ?>...</small></td>
                                    <td><small><?php echo date('M d, H:i', strtotime($u['last_login_at'])); ?></small></td>
                                    <td><?php echo sprintf("%02d:%02d", $hours, $mins); ?></td>
                                    <td>
                                        <?php if ($u['is_blocked']): ?>
                                            <span class="badge bg-danger">BLOCKED</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">ACTIVE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['is_blocked']): ?>
                                            <a href="?action=unblock&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-success">Unblock</a>
                                        <?php else: ?>
                                            <a href="?action=block&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Block this user?')">Block</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
