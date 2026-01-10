<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config.php';

try {
    $raw_key = $_POST['key'] ?? $_GET['key'] ?? $_POST['license_key'] ?? $_GET['license_key'] ?? '';
    $key = strtoupper(trim($raw_key));
    $device_id = trim($_POST['device_id'] ?? $_GET['device_id'] ?? '');

    if (empty($key)) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Key is required"]);
        exit;
    }

    global $pdo;
    
    // Database check
    $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE UPPER(license_key) = ? LIMIT 1");
    $stmt->execute([$key]);
    $license = $stmt->fetch();

    if (!$license) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Invalid license key"]);
        exit;
    }

    // Check if key is active
    if (strtoupper($license['status']) !== 'ACTIVE') {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "License is " . $license['status']]);
        exit;
    }

    // Expiry check - Multi-timezone safe (IST)
    $timezone = new DateTimeZone('Asia/Kolkata');
    $now = new DateTime('now', $timezone);
    $expiry = new DateTime($license['expires_at'], $timezone);

    if ($now > $expiry) {
        $update = $pdo->prepare("UPDATE license_keys SET status = 'expired' WHERE id = ?");
        $update->execute([$license['id']]);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "License key has expired"]);
        exit;
    }

    // Check if user already exists in app_users
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM app_users WHERE license_key = ?");
    $stmt->execute([$key]);
    $user_exists = $stmt->fetchColumn() > 0;

    // Device Binding check
    if (!empty($device_id)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM app_users WHERE license_key = ? AND device_id = ?");
        $stmt->execute([$key, $device_id]);
        $already_bound = $stmt->fetchColumn() > 0;

        if (!$already_bound) {
             $stmt = $pdo->prepare("SELECT COUNT(DISTINCT device_id) FROM app_users WHERE license_key = ?");
             $stmt->execute([$key]);
             $current_devices = $stmt->fetchColumn();
             $max_devices = (int)($license['max_devices'] ?? 1);
             
             if ($current_devices >= $max_devices) {
                 ob_clean();
                 echo json_encode(["status" => "error", "message" => "Max devices reached ($max_devices)"]);
                 exit;
             }
        }
    }

    // Success response with user_exists flag
    ob_clean();
    echo json_encode([
        "status" => "success",
        "expires_at" => $license['expires_at'],
        "user_exists" => (bool)$user_exists
    ]);
    exit;

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Server error"]);
    exit;
}
?>