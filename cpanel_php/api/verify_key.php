<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config.php';

try {
    $raw_key = $_POST['key'] ?? $_GET['key'] ?? $_POST['license_key'] ?? $_GET['license_key'] ?? '';
    $key = strtoupper(trim($raw_key));

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

    // Check expiry
    $expiry_time = strtotime($license['expires_at']);
    if (time() > $expiry_time) {
        $update = $pdo->prepare("UPDATE license_keys SET status = 'expired' WHERE id = ?");
        $update->execute([$license['id']]);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "License key has expired"]);
        exit;
    }

    // Success response
    ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "Key valid",
        "expires_at" => date('Y-m-d H:i:s', $expiry_time)
    ]);
    exit;

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Server error"]);
    exit;
}
