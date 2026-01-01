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

        echo json_encode([
            "global_control" => [
                "app_status" => $all_config['app_status'] ?? 'ON',
                "maintenance_message" => $all_config['maintenance_message'] ?? '',
                "force_logout" => ($all_config['force_logout_flag'] ?? 'no') === 'yes'
            ],
            "announcement" => $ann ? [
                "id" => $ann['id'],
                "title" => $ann['title'],
                "message" => $ann['message'],
                "priority" => $ann['priority'] ?? 'normal',
                "type" => $ann['type']
            ] : null,
            "panels" => $panels
        ]);
    } 
    elseif ($action === 'apps') {
        $stmt = $pdo->query("SELECT a.package_name, a.app_name, a.icon_url, a.visibility, v.version_name, v.version_code, v.apk_url 
                             FROM apps a 
                             LEFT JOIN app_versions v ON a.id = v.app_id AND v.is_latest = TRUE
                             WHERE a.is_enabled = TRUE AND a.visibility != 'hidden'");
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