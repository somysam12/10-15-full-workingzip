<?php
/**
 * Silent Panel - User Registration API
 * FINAL STABLE API CONTRACT
 */

ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config.php';

try {
    $key = strtoupper(trim($_POST['license_key'] ?? $_POST['key'] ?? ''));
    $name = trim($_POST['user_name'] ?? '');
    $device = trim($_POST['device_id'] ?? '');

    if ($key === '' || $name === '' || $device === '') {
        ob_clean();
        echo json_encode(["status"=>"error","message"=>"Missing fields"]);
        exit;
    }

    global $pdo;

    // Check if user is blocked
    $stmt = $pdo->prepare("SELECT is_blocked FROM app_users WHERE UPPER(license_key) = ? AND device_id = ? LIMIT 1");
    $stmt->execute([$key, $device]);
    $user = $stmt->fetch();

    if ($user && $user['is_blocked']) {
        ob_clean();
        echo json_encode(["status"=>"blocked"]);
        exit;
    }

    // Register/Update User
    $stmt = $pdo->prepare("
        INSERT INTO app_users (license_key, user_name, device_id, first_login_at, last_login_at)
        VALUES (?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE user_name = VALUES(user_name), last_login_at = NOW()
    ");
    $stmt->execute([$key, $name, $device]);

    ob_clean();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status"=>"error"]);
}
exit;
