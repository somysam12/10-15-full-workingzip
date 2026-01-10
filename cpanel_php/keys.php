<?php
ob_start(); // Buffer output to prevent 500 errors if something triggers a warning
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (!file_exists('config.php')) {
        throw new Exception("config.php is missing");
    }
    require_once 'config.php';
    requireLogin();

    global $pdo;
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection (\$pdo) is not initialized.");
    }

    // Handle Actions
    if (isset($_GET['action'])) {
        $id = (int)$_GET['id'];
        if ($_GET['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM license_keys WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: keys.php?success=Key deleted");
            exit;
        } elseif ($_GET['action'] === 'reset') {
            $stmt = $pdo->prepare("UPDATE license_keys SET device_id = NULL WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: keys.php?success=Device reset successfully");
            exit;
        } elseif ($_GET['action'] === 'block') {
            $stmt = $pdo->prepare("UPDATE license_keys SET status = 'banned' WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: keys.php?success=Key blocked");
            exit;
        } elseif ($_GET['action'] === 'unblock') {
            $stmt = $pdo->prepare("UPDATE license_keys SET status = 'active' WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: keys.php?success=Key unblocked");
            exit;
        }
    }

    // Handle Generate
    if (isset($_POST['generate'])) {
        $count = (int)$_POST['count'];
        $duration = (int)$_POST['duration']; 
        $unit = $_POST['duration_unit']; // hours, days, months
        
        for ($i = 0; $i < $count; $i++) {
            $key = "SHASH-" . strtoupper(bin2hex(random_bytes(4)));
            $expiry = date('Y-m-d H:i:s', strtotime("+$duration $unit"));
            $stmt = $pdo->prepare("INSERT INTO license_keys (license_key, expires_at) VALUES (?, ?)");
            $stmt->execute([$key, $expiry]);
        }
        header("Location: keys.php?success=$count keys generated");
        exit;
    }

    $keys = $pdo->query("SELECT * FROM license_keys ORDER BY created_at DESC")->fetchAll();

} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: text/plain');
    die("Critical Page Error: " . $e->getMessage() . "\n\nStack Trace:\n" . $e->getTraceAsString());
}

include 'header.php';
include 'sidebar.php';
?>

<div class="main-content d-flex flex-column align-items-center justify-content-center min-vh-100 p-0 m-0" style="margin-left: 0 !important; width: 100% !important;">
    <div class="container-fluid d-flex justify-content-center align-items-center px-3 px-md-4" style="max-width: 1200px; width: 100%;">
        <div class="row w-100 justify-content-center m-0">
            <div class="col-12">
                <div class="card bg-dark text-white border-secondary shadow-lg mx-auto w-100">
                    <div class="card-header border-secondary d-flex flex-wrap justify-content-between align-items-center bg-gradient gap-3 py-3 px-4">
                        <h4 class="mb-0 fs-5 fs-md-4 text-center text-sm-start w-100 w-sm-auto"><i class="fas fa-key me-2 text-primary"></i>License Key Management</h4>
                        <button class="btn btn-primary shadow-sm mx-auto mx-sm-0 px-4" data-bs-toggle="modal" data-bs-target="#generateModal">
                            <i class="fas fa-plus-circle me-1"></i> GENERATE KEYS
                        </button>
                    </div>
                    <div class="card-body p-0 p-md-3">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success m-3 text-center">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="table-responsive w-100">
                            <table class="table table-dark table-hover align-middle mb-0 text-center w-100">
                                <thead class="table-light text-dark">
                                    <tr>
                                        <th class="text-center">Key</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center d-none d-md-table-cell">Expires At</th>
                                        <th class="text-center d-none d-sm-table-cell">Device ID</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($keys as $k): ?>
                                        <tr>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <code class="text-info me-2 fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($k['license_key']); ?></code>
                                                    <button class="btn btn-sm btn-outline-secondary border-0 p-1" onclick="copyToClipboard('<?php echo $k['license_key']; ?>')" title="Copy Key">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($k['status'] == 'active'): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.75rem;">ACTIVE</span>
                                                <?php elseif ($k['status'] == 'banned'): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.75rem;">BANNED</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" style="font-size: 0.75rem;">EXPIRED</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center d-none d-md-table-cell"><small><?php echo date('M d, Y H:i', strtotime($k['expires_at'])); ?></small></td>
                                            <td class="text-center d-none d-sm-table-cell">
                                                <?php if ($k['device_id']): ?>
                                                    <small class="text-muted"><?php echo substr($k['device_id'], 0, 8); ?>...</small>
                                                    <a href="?action=reset&id=<?php echo $k['id']; ?>" class="ms-1 text-warning" title="Reset Device"><i class="fas fa-redo-alt"></i></a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Not Bound</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <?php if ($k['status'] == 'active'): ?>
                                                        <a href="?action=block&id=<?php echo $k['id']; ?>" class="btn btn-sm btn-outline-warning" title="Block"><i class="fas fa-ban"></i></a>
                                                    <?php else: ?>
                                                        <a href="?action=unblock&id=<?php echo $k['id']; ?>" class="btn btn-sm btn-outline-success" title="Unblock"><i class="fas fa-check"></i></a>
                                                    <?php endif; ?>
                                                    <a href="?action=delete&id=<?php echo $k['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanent delete?')" title="Delete"><i class="fas fa-trash"></i></a>
                                                </div>
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
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary shadow">
            <form method="POST">
                <div class="modal-header border-secondary bg-primary bg-opacity-10">
                    <h5 class="modal-title"><i class="fas fa-magic me-2"></i>Generate New Keys</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Number of Keys</label>
                        <input type="number" name="count" class="form-control bg-secondary text-white border-0 py-2" value="1" min="1" max="500">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Duration Value</label>
                            <input type="number" name="duration" class="form-control bg-secondary text-white border-0 py-2" value="1" min="1">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Duration Unit</label>
                            <select name="duration_unit" class="form-select bg-secondary text-white border-0 py-2">
                                <option value="hours">Hours</option>
                                <option value="days" selected>Days</option>
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                                <option value="years">Years</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary p-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="generate" class="btn btn-primary px-5">Generate Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    if (!navigator.clipboard) {
        // Fallback for non-HTTPS or older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert('Key copied to clipboard!');
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textArea);
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        alert('Key copied to clipboard!');
    }, (err) => {
        console.error('Async: Could not copy text: ', err);
    });
}
</script>

<?php include 'footer.php'; ?>
