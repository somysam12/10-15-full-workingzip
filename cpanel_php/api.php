<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $all_config = getAllConfig();
    $ann = getActiveAnnouncement();

    $apps_stmt = $pdo->query("
        SELECT
            a.id,
            a.app_name AS name,
            a.packageName,      -- Added
            a.iconUrl,          -- Added
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
    $apps = $apps_stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        "status" => "success",
        "config" => [
            "announcement" => $ann['message'] ?? 'Welcome!',
            "isMaintenance" => ($all_config['app_status'] ?? 'ON') === 'OFF',
            "mainLogoUrl" => $all_config['main_logo_url'] ?? '', // Added for main logo
            "apks" => $apps
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>