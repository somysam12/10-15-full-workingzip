<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';

try {
    $key = strtoupper(trim($_POST['license_key'] ?? ''));
    $device = trim($_POST['device_id'] ?? '');

    if ($key === '' || $device === '') {
        ob_clean();
        echo json_encode(["status"=>"error","message"=>"Missing fields"]);
        exit;
    }

    global $pdo;

    // Check if blocked
    $stmt = $pdo->prepare("SELECT is_blocked FROM app_users WHERE license_key = ? AND device_id = ? LIMIT 1");
    $stmt->execute([$key, $device]);
    $user = $stmt->fetch();

    if ($user && $user['is_blocked']) {
        ob_clean();
        echo json_encode(["status" => "blocked", "message" => "You are blocked"]);
        exit;
    }

    // Update usage
    $stmt = $pdo->prepare("
        UPDATE app_users 
        SET last_login_at = NOW(), total_usage_seconds = total_usage_seconds + 60
        WHERE license_key=? AND device_id=? AND is_blocked=0
    ");
    $stmt->execute([$key, $device]);

    ob_clean();
    echo json_encode(["status"=>"success"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status"=>"error"]);
}
exit;
