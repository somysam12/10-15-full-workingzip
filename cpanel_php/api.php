<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $req_app_type = $_GET['app_type'] ?? 'master';
    
    // Smart Analytics: Track Access
    if (isset($_GET['track']) && $_GET['track'] === 'true') {
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];
        
        if (isset($_GET['app_id'])) {
            $stmt = $pdo->prepare("INSERT INTO download_stats (app_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $stmt->execute([(int)$_GET['app_id'], $ip, $ua]);
        }
    }

    // Maintenance Keys
    $status_key = ($req_app_type === 'panel') ? 'panel_maintenance' : 'master_maintenance';
    $maint_msg_key = ($req_app_type === 'panel') ? 'panel_maintenance_msg' : 'master_maintenance_msg';
    $logo_key = ($req_app_type === 'panel') ? 'panel_logo_url' : 'main_logo_url';
    
    // Check Cache Reset Signal
    $reset_cache_flag = getConfig('reset_cache_flag_' . $req_app_type, '0');
    
    $isMaintenance = (getConfig($status_key, 'OFF') === 'ON');
    $logoUrl = getConfig($logo_key, '');
    $maintenanceMessage = getConfig($maint_msg_key, 'System is under maintenance.');

    $stmt_ann = $pdo->prepare("SELECT * FROM announcements WHERE active = 1 AND (app_type = ? OR app_type = 'all') ORDER BY id DESC LIMIT 1");
    $stmt_ann->execute([$req_app_type]);
    $ann = $stmt_ann->fetch();

    $response = [
        "status" => "success",
        "config" => [
            "announcement" => $ann['message'] ?? 'Welcome!',
            "isMaintenance" => $isMaintenance,
            "maintenanceMessage" => $maintenanceMessage,
            "mainLogoUrl" => $logoUrl,
            "cacheResetSignal" => $reset_cache_flag,
            "security" => [
                "forceLogout" => false
            ]
        ]
    ];

    if ($req_app_type === 'master') {
        $apps_stmt = $pdo->query("
            SELECT
                a.id, a.app_name AS name, a.packageName, a.iconUrl,
                v.version_name AS latestVersion, v.apk_url AS downloadUrl,
                v.version_code AS versionCode, v.created_at AS lastUpdated
            FROM apps a
            LEFT JOIN app_versions v ON a.id = v.app_id AND v.is_latest = 1
            ORDER BY a.id DESC
        ");
        
        $apps = array_map(function($row) {
            $row['id'] = (string)$row['id'];
            $row['versionCode'] = (int)$row['versionCode'];
            return $row;
        }, $apps_stmt->fetchAll(PDO::FETCH_ASSOC));
        $response['config']['apks'] = $apps;

    } else {
        $response['config']['panels'] = getAllPanels();
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>