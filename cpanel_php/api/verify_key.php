<?php
/**
 * Silent Panel - Key Verification API
 * STRICT JSON RESPONSE ONLY
 */

// Phase 1: Ensure JSON purity
ob_start(); 
error_reporting(0); // Disable all error reporting to avoid polluting JSON output
ini_set('display_errors', 0);

require_once '../config.php';

// Prepare header
header('Content-Type: application/json; charset=utf-8');

try {
    // Phase 2: Normalize key (trim + uppercase)
    $raw_key = $_POST['key'] ?? $_GET['key'] ?? $_POST['license_key'] ?? $_GET['license_key'] ?? '';
    $key = strtoupper(trim($raw_key));
    $device_id = trim($_POST['device_id'] ?? $_GET['device_id'] ?? '');

    if (empty($key)) {
        ob_clean();
        echo json_encode([
            "status" => "error",
            "message" => "Key is required"
        ]);
        exit;
    }

    global $pdo;
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    // Phase 1: Database check with normalized key
    $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE UPPER(license_key) = ? LIMIT 1");
    $stmt->execute([$key]);
    $license = $stmt->fetch();

    if (!$license) {
        ob_clean();
        echo json_encode([
            "status" => "error",
            "message" => "Invalid license key"
        ]);
        exit;
    }

    // Status check
    if (strtoupper($license['status']) !== 'ACTIVE') {
        ob_clean();
        echo json_encode([
            "status" => "error",
            "message" => "License is " . $license['status']
        ]);
        exit;
    }

    // Expiry check
    $expiry_time = strtotime($license['expires_at']);
    if (time() > $expiry_time) {
        // Auto-update status to expired
        $update = $pdo->prepare("UPDATE license_keys SET status = 'expired' WHERE id = ?");
        $update->execute([$license['id']]);
        
        ob_clean();
        echo json_encode([
            "status" => "error",
            "message" => "License key has expired"
        ]);
        exit;
    }

    // Phase 3: Exact Response Format
    ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "Key valid",
        "expires_at" => date('Y-m-d H:i:s', $expiry_time)
    ]);
    exit;

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        "status" => "error",
        "message" => "Server error occurred"
    ]);
    exit;
}
?>