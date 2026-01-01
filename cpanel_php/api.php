<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $all_config = getAllConfig();
    $ann = getActiveAnnouncement();

    // The only change is ensuring latestVersion is a string
    $apps_stmt = $pdo->query("
        SELECT
            a.id,
            a.app_name AS name,
            a.packageName,
            a.iconUrl,
            v.version_name AS latestVersion,
            v.apk_url AS downloadUrl,
            c.name AS category,
            v.version_code AS versionCode,
            v.created_at AS lastUpdated
        FROM apps a
        LEFT JOIN app_versions v ON a.id = v.app_id AND v.is_latest = 1
        LEFT JOIN categories c ON a.category_id = c.id
        ORDER BY a.id DESC
    ");
    
    // Ensure all fields are correctly typed for JSON
    $apps = array_map(function($row) {
        $row['id'] = (string)$row['id'];
        $row['versionCode'] = (int)$row['versionCode'];
        $row['latestVersion'] = (string)$row['latestVersion'];
        return $row;
    }, $apps_stmt->fetchAll(PDO::FETCH_ASSOC));

    $response = [
        "status" => "success",
        "config" => [
            "announcement" => $ann['message'] ?? 'Welcome!',
            "isMaintenance" => ($all_config['app_status'] ?? 'ON') === 'OFF',
            "mainLogoUrl" => $all_config['main_logo_url'] ?? '',
            "apks" => $apps
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>