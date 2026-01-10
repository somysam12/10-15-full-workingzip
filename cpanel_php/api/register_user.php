<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';

try {
    $key = strtoupper(trim($_POST['license_key'] ?? ''));
    $name = trim($_POST['user_name'] ?? '');
    $device = trim($_POST['device_id'] ?? '');

    if ($key === '' || $name === '' || $device === '') {
        ob_clean();
        echo json_encode(["status"=>"error","message"=>"Missing fields"]);
        exit;
    }

    global $pdo;

    // First check if key exists and is ACTIVE in license_keys
    $stmt = $pdo->prepare("SELECT status FROM license_keys WHERE UPPER(license_key) = ? LIMIT 1");
    $stmt->execute([$key]);
    $license = $stmt->fetch();

    if (!$license || strtoupper($license['status']) !== 'ACTIVE') {
        ob_clean();
        echo json_encode(["status"=>"error","message"=>"Key is not active or invalid"]);
        exit;
    }

    // Now handle registration
    // MySQL ON DUPLICATE KEY UPDATE for cPanel
    $stmt = $pdo->prepare("
        INSERT INTO app_users (license_key, user_name, device_id, first_login_at, last_login_at)
        VALUES (?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE user_name = VALUES(user_name), last_login_at = NOW()
    ");
    $stmt->execute([$key, $name, $device]);

    // Check if the user was already blocked in app_users
    $stmt = $pdo->prepare("SELECT is_blocked FROM app_users WHERE license_key = ? AND device_id = ? LIMIT 1");
    $stmt->execute([$key, $device]);
    $user = $stmt->fetch();

    if ($user && $user['is_blocked']) {
        ob_clean();
        echo json_encode(["status"=>"blocked","message"=>"You are blocked"]);
        exit;
    }

    ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "User registered"
    ]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        "status"=>"error",
        "message"=>"Server error"
    ]);
}
exit;
