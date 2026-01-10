<?php
/**
 * Silent Panel - Heartbeat API
 * FINAL STABLE API CONTRACT
 */

ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config.php';

try {
    $key = strtoupper(trim($_POST['license_key'] ?? $_POST['key'] ?? ''));
    $device = trim($_POST['device_id'] ?? '');

    if ($key === '' || $device === '') {
        ob_clean();
        echo json_encode(["status"=>"error"]);
        exit;
    }

    global $pdo;

    // Check block status
    $stmt = $pdo->prepare("SELECT is_blocked FROM app_users WHERE UPPER(license_key) = ? AND device_id = ? LIMIT 1");
    $stmt->execute([$key, $device]);
    $user = $stmt->fetch();

    if ($user && $user['is_blocked']) {
        ob_clean();
        echo json_encode(["status" => "blocked"]);
        exit;
    }

    // Update session duration
    $stmt = $pdo->prepare("
        UPDATE user_sessions 
        SET session_end = CURRENT_TIMESTAMP,
            duration_seconds = EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - session_start))
        WHERE license_key = ? AND device_id = ? AND session_end IS NULL
    ");
    $stmt->execute([$key, $device]);

    // Update total usage in app_users
    $stmt = $pdo->prepare("
        UPDATE app_users 
        SET total_usage_seconds = (
            SELECT COALESCE(SUM(duration_seconds), 0) 
            FROM user_sessions 
            WHERE license_key = ? AND device_id = ?
        ),
        last_login_at = CURRENT_TIMESTAMP
        WHERE UPPER(license_key) = ? AND device_id = ?
    ");
    $stmt->execute([$key, $device, $key, $device]);

    ob_clean();
    echo json_encode(["status"=>"success"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status"=>"error"]);
}
exit;
