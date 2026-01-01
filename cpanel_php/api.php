<?php
require_once 'config.php';

// API System (FOR APP COMMUNICATION)
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'config';

try {
    // Log request for analytics
    logApiRequest($action);

    if ($action === 'config') {
        $all_config = getAllConfig();
        $ann = getActiveAnnouncement();
        $panels = getAllPanels();
        
        $protocol = request_protocol();
        $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

        // Format to match Android ConfigManager.java expectations
        echo json_encode([
            "status" => "success",
            "app_config" => [
                "app_status" => $all_config['app_status'] ?? 'ON',
                "maintenance_message" => $all_config['maintenance_message'] ?? '',
                "latest_version" => $all_config['latest_version'] ?? '1.0.0',
                "update_url" => $all_config['update_url'] ?? '',
                "announcement_title" => $ann['title'] ?? '',
                "announcement_message" => $ann['message'] ?? '',
                "announcement_active" => $ann ? true : false
            ],
            "panels" => array_map(function($p) {
                return [
                    "name" => $p['name'],
                    "url" => $p['url'],
                    "site_key" => $p['site_key']
                ];
            }, $panels)
        ]);
    } 
    elseif ($action === 'apps') {
        $stmt = $pdo->query("SELECT a.id, a.app_name, a.category_id, v.version_name, v.version_code, v.apk_url 
                             FROM apps a 
                             LEFT JOIN app_versions v ON a.id = v.app_id AND v.is_latest = 1
                             ORDER BY a.id DESC");
        echo json_encode($stmt->fetchAll());
    }
    elseif ($action === 'toggle_server') {
        // Admin action (needs login or token)
        if (!isLoggedIn() && !validateApiToken()) {
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

function request_protocol() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http";
}
?>