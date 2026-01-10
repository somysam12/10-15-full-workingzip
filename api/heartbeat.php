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

    // Update usage
    $stmt = $pdo->prepare("
        UPDATE app_users 
        SET last_login_at = NOW(), total_usage_seconds = total_usage_seconds + 60
        WHERE UPPER(license_key)=? AND device_id=? AND is_blocked=0
    ");
    $stmt->execute([$key, $device]);

    // Track Session
    $stmt = $pdo->prepare("SELECT id FROM user_sessions WHERE license_key = ? AND device_id = ? AND session_end IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([$key, $device]);
    $session = $stmt->fetch();
    
    if ($session) {
        $stmt = $pdo->prepare("UPDATE user_sessions SET duration_seconds = duration_seconds + 60, last_heartbeat = NOW() WHERE id = ?");
        $stmt->execute([$session['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_sessions (license_key, device_id, duration_seconds, login_time, last_heartbeat) VALUES (?, ?, 60, NOW(), NOW())");
        $stmt->execute([$key, $device]);
    }

    ob_clean();
    echo json_encode(["status"=>"success"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["status"=>"error"]);
}
exit;
