<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Determine which app is requesting config
    $req_app_type = $_GET['app_type'] ?? 'master';
    
    // App-specific status and logo
    $status_key = ($req_app_type === 'panel') ? 'panel_app_status' : 'app_status';
    $logo_key = ($req_app_type === 'panel') ? 'panel_logo_url' : 'main_logo_url';
    $maint_msg_key = ($req_app_type === 'panel') ? 'panel_maintenance_msg' : 'maintenance_msg';
    
    $isMaintenance = (getConfig($status_key, 'ON') === 'OFF');
    $logoUrl = getConfig($logo_key, '');
    $maintenanceMessage = getConfig($maint_msg_key, 'System is under maintenance.');

    // App-specific announcement
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
        ]
    ];

    if ($req_app_type === 'master') {
        // --- START: SIMPLIFIED AND FIXED QUERY ---
        $apps_stmt = $pdo->query("
            SELECT
                a.id, a.app_name AS name, a.packageName, a.iconUrl,
                v.version_name AS latestVersion, v.apk_url AS downloadUrl,
                v.version_code AS versionCode, v.created_at AS lastUpdated
            FROM apps a
            LEFT JOIN app_versions v ON a.id = v.app_id AND v.is_latest = 1
            ORDER BY a.id DESC
        ");

        if ($apps_stmt === false) {
            throw new Exception('Database query for fetching apps failed.');
        }
        // --- END: SIMPLIFIED AND FIXED QUERY ---
        
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