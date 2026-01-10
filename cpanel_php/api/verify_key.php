<?php
require_once '../config.php';
header('Content-Type: application/json');

$key = $_POST['license_key'] ?? $_GET['license_key'] ?? '';
$device_id = $_POST['device_id'] ?? $_GET['device_id'] ?? '';

if (empty($key)) {
    echo json_encode(['status' => 'invalid', 'message' => 'Key is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE license_key = ?");
    $stmt->execute([$key]);
    $license = $stmt->fetch();

    if (!$license) {
        echo json_encode(['status' => 'invalid', 'message' => 'License key not found']);
        exit;
    }

    if ($license['status'] !== 'active') {
        echo json_encode(['status' => 'invalid', 'message' => 'License key is ' . $license['status']]);
        exit;
    }

    $now = new DateTime();
    $expiry = new DateTime($license['expires_at']);
    if ($now > $expiry) {
        $stmt = $pdo->prepare("UPDATE license_keys SET status = 'expired' WHERE id = ?");
        $stmt->execute([$license['id']]);
        echo json_encode(['status' => 'invalid', 'message' => 'License key has expired']);
        exit;
    }

    // Optional: Device Binding
    if (!empty($device_id)) {
        if (empty($license['device_id'])) {
            $stmt = $pdo->prepare("UPDATE license_keys SET device_id = ? WHERE id = ?");
            $stmt->execute([$device_id, $license['id']]);
        } elseif ($license['device_id'] !== $device_id) {
            echo json_encode(['status' => 'invalid', 'message' => 'Device mismatch']);
            exit;
        }
    }

    // Maintenance & Announcement status
    $config = getAllConfig();

    echo json_encode([
        'status' => 'valid',
        'expiry' => $license['expires_at'],
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
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
