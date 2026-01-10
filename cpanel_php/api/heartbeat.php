<?php
ob_start();
require_once '../config.php';
header('Content-Type: application/json');

$key = strtoupper(trim($_POST['license_key'] ?? $_POST['key'] ?? ''));
$device_id = trim($_POST['device_id'] ?? '');

if (empty($key) || empty($device_id)) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

try {
    global $pdo;
    
    // Check block status
    $stmt = $pdo->prepare("SELECT is_blocked FROM app_users WHERE license_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $user = $stmt->fetch();
    
    if ($user && $user['is_blocked']) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Blocked", "blocked" => true]);
        exit;
    }

    // Update usage (ping every 60s)
    $stmt = $pdo->prepare("UPDATE app_users SET total_usage_seconds = total_usage_seconds + 60, last_login_at = NOW() WHERE license_key = ?");
    $stmt->execute([$key]);

    ob_clean();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status" => "error"]);
}
exit;
