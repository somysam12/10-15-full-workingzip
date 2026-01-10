<?php
ob_start();
require_once '../config.php';
header('Content-Type: application/json');

$key = strtoupper(trim($_POST['license_key'] ?? $_POST['key'] ?? ''));
$device_id = trim($_POST['device_id'] ?? '');
$user_name = trim($_POST['user_name'] ?? '');

if (empty($key) || empty($device_id) || empty($user_name)) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

try {
    global $pdo;
    
    // Check if key exists and is active
    $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE UPPER(license_key) = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$key]);
    $license = $stmt->fetch();
    
    if (!$license) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Invalid or inactive key"]);
        exit;
    }

    // Check if user is blocked
    $stmt = $pdo->prepare("SELECT is_blocked FROM app_users WHERE license_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $user = $stmt->fetch();
    
    if ($user && $user['is_blocked']) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "User is blocked", "blocked" => true]);
        exit;
    }

    if (!$user) {
        // First time login - Insert
        $stmt = $pdo->prepare("INSERT INTO app_users (license_key, user_name, device_id, first_login_at, last_login_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$key, $user_name, $device_id]);
    } else {
        // Update last login
        $stmt = $pdo->prepare("UPDATE app_users SET last_login_at = NOW(), device_id = ? WHERE license_key = ?");
        $stmt->execute([$device_id, $key]);
    }

    ob_clean();
    echo json_encode(["status" => "success", "message" => "User registered"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Server error"]);
}
exit;
