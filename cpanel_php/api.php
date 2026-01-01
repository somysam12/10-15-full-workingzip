<?php
require_once 'config.php';

// API System (FOR APP COMMUNICATION)
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'config';

try {
    // This function is still present in your code, but the API doesn't use it.
    // I'm leaving it here to avoid breaking anything.
    if (function_exists('logApiRequest')) {
        logApiRequest($action);
    }

    if ($action === 'config') {
        // --- THIS IS THE FIXED ENDPOINT ---

        // 1. Get existing configuration data (your original code)
        $all_config = getAllConfig();
        $ann = getActiveAnnouncement();

        // 2. Fetch the list of apps (merged from your 'apps' endpoint)
        // This query now uses aliases to match the Android app's data model
        $apps_stmt = $pdo->query("
            SELECT
                a.id,
                a.app_name AS name,
                '' as iconUrl, /* Placeholder for icon URL */
                v.version_name AS latestVersion,
                v.apk_url AS downloadUrl,
                (SELECT name FROM categories WHERE id = a.category_id) AS category,
                v.version_code AS versionCode,
                v.created_at AS lastUpdated
            FROM apps a
            LEFT JOIN app_versions v ON a.id = v.app_id AND v.is_latest = 1
            ORDER BY a.id DESC
        ");
        $apps = $apps_stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Create the single, unified response your Android app needs
        $response = [
            "status" => "success",
            "config" => [
                "announcement" => $ann['message'] ?? 'Welcome!',
                "isMaintenance" => ($all_config['app_status'] ?? 'ON') === 'OFF',
                "apks" => $apps // The app list is now included here
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
    } 
    elseif ($action === 'apps') {
        // --- THIS ENDPOINT IS UNCHANGED ---
        // Your original logic for the 'apps' action is preserved for any other system that might use it.
        $stmt = $pdo->query("SELECT a.id, a.app_name, a.category_id, v.version_name, v.version_code, v.apk_url 
                             FROM apps a 
                             LEFT JOIN app_versions v ON a.id = v.app_id AND v.is_latest = 1
                             ORDER BY a.id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($action === 'toggle_server') {
        // --- THIS ENDPOINT IS UNCHANGED ---
        if (!function_exists('isLoggedIn') || (!isLoggedIn() && !validateApiToken())) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        setConfig('app_status', $data['enabled'] ? 'ON' : 'OFF');
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// This function is also preserved from your original code.
if (!function_exists('request_protocol')) {
    function request_protocol() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http";
    }
}
?>