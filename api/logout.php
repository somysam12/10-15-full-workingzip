<?php
// api/logout.php
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
        exit;
    }

    global $pdo;

    // Find the most recent, active session for this user/device that has NOT been closed.
    $stmt = $pdo->prepare("
        UPDATE user_sessions 
        SET session_end = NOW() 
        WHERE license_key = ? AND device_id = ? AND session_end IS NULL
    ");
    $stmt->execute([$key, $device]);

    ob_clean();
    echo json_encode(['status' => 'success', 'message' => 'Session closed.']);
} catch (Exception $e) {
    ob_clean();
}
exit;
