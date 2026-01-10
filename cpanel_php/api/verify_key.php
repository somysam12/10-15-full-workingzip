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
    
    // 1. Get License Data
    $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE UPPER(license_key) = ? LIMIT 1");
    $stmt->execute([$key]);
    $license = $stmt->fetch();

    if (!$license) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Invalid license key"]);
        exit;
    }

    // 2. Status check
    if (strtoupper($license['status']) !== 'ACTIVE') {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "License is " . $license['status']]);
        exit;
    }

    // 3. Expiry check (IST)
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

    // 4. Check if user profile exists
    $stmt = $pdo->prepare("SELECT is_blocked FROM app_users WHERE UPPER(license_key) = ? LIMIT 1");
    $stmt->execute([$key]);
    $user = $stmt->fetch();
    
    $user_exists = (bool)$user;

    // 5. Block check
    if ($user && $user['is_blocked']) {
        ob_clean();
        echo json_encode(["status" => "blocked"]);
        exit;
    }

    // 6. Device Limit Enforcement
    if (!empty($device_id)) {
        // Is this device already known for this key?
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM app_users WHERE UPPER(license_key) = ? AND device_id = ?");
        $stmt->execute([$key, $device_id]);
        $device_recognized = $stmt->fetchColumn() > 0;

        if (!$device_recognized) {
             // Check if limit reached
             $stmt = $pdo->prepare("SELECT COUNT(DISTINCT device_id) FROM app_users WHERE UPPER(license_key) = ?");
             $stmt->execute([$key]);
             $current_devices = $stmt->fetchColumn();
             
             $max_allowed = (int)($license['max_devices'] ?? 1);
             
             if ($current_devices >= $max_allowed) {
                 ob_clean();
                 echo json_encode(["status" => "error", "message" => "Max devices reached ($max_allowed)"]);
                 exit;
             }
        }
    }

    // 7. Success Response - RIGID CONTRACT
    ob_clean();
    echo json_encode([
        "status" => "success",
        "expires_at" => $license['expires_at'],
        "user_exists" => $user_exists
    ]);
    exit;

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Server error"]);
    exit;
}
