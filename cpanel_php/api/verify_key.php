<?php
ob_start(); // Prevent any accidental output
require_once '../config.php';
header('Content-Type: application/json');

// Get and clean input
$key = trim($_POST['license_key'] ?? $_GET['license_key'] ?? '');
$device_id = trim($_POST['device_id'] ?? $_GET['device_id'] ?? '');

if (empty($key)) {
    echo json_encode(['status' => 'invalid', 'message' => 'Key is required']);
    exit;
}

try {
    // Case-insensitive match using UPPER()
    $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE UPPER(license_key) = UPPER(?)");
    $stmt->execute([$key]);
    $license = $stmt->fetch();

    if (!$license) {
        echo json_encode(['status' => 'invalid', 'message' => 'License key not found']);
        exit;
    }

    // Status check (case-insensitive)
    if (strtolower($license['status'] ?? '') !== 'active') {
        echo json_encode(['status' => 'invalid', 'message' => 'License key is ' . ($license['status'] ?? 'unknown')]);
        exit;
    }

    // Expiry check
    $now = time();
    $expiry_time = strtotime($license['expires_at']);
    if ($now > $expiry_time) {
        $stmt = $pdo->prepare("UPDATE license_keys SET status = 'expired' WHERE id = ?");
        $stmt->execute([$license['id']]);
        echo json_encode(['status' => 'invalid', 'message' => 'License key has expired']);
        exit;
    }

    // Device Binding
    if (!empty($device_id)) {
        if (empty($license['device_id'])) {
            $stmt = $pdo->prepare("UPDATE license_keys SET device_id = ? WHERE id = ?");
            $stmt->execute([$device_id, $license['id']]);
        } elseif ($license['device_id'] !== $device_id) {
            echo json_encode(['status' => 'invalid', 'message' => 'Device mismatch']);
            exit;
        }
    }

    $config = getAllConfig();

    // Clean JSON response
    ob_clean();
    echo json_encode([
        'status' => 'success',
        'expires_at' => date('Y-m-d H:i', $expiry_time),
        'message' => 'Valid license',
        'maintenance' => [
            'enabled' => ($config['maintenance_enabled'] ?? 'false') === 'true',
            'message' => $config['maintenance_message'] ?? ''
        ],
        'announcement' => [
            'enabled' => ($config['announcement_enabled'] ?? 'false') === 'true',
            'title' => $config['announcement_title'] ?? '',
            'message' => $config['announcement_message'] ?? '',
            'type' => $config['announcement_type'] ?? 'info'
        ]
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Server Error']);
}
exit;
?>