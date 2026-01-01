<?php
require_once 'config.php';
header('Content-Type: application/json');

    // Action handling
    $action = $_GET['action'] ?? '';
    if ($action === 'toggle_server') {
        $data = json_decode(file_get_contents('php://input'), true);
        $status = (isset($data['enabled']) && $data['enabled']) ? 'ON' : 'OFF';
        setConfig('app_status', $status);
        echo json_encode(['success' => true, 'status' => $status]);
        exit;
    }

    $all_config = getAllConfig();
    
    // Filter announcement by app_type if provided
    $req_app_type = $_GET['app_type'] ?? 'all';
    try {
        // Try the specific query first
        $stmt_ann = $pdo->prepare("SELECT * FROM announcements WHERE active = 1 AND (app_type = ? OR app_type = 'all') ORDER BY id DESC LIMIT 1");
        $stmt_ann->execute([$req_app_type]);
        $ann = $stmt_ann->fetch();
        
        // If query succeeded but returned nothing, or returned a mismatched app_type (shouldn't happen with the SQL above but just in case)
        if (!$ann) {
             $stmt_ann = $pdo->query("SELECT * FROM announcements WHERE active = 1 ORDER BY id DESC LIMIT 1");
             $ann = $stmt_ann->fetch();
        }
    } catch (Exception $e) {
        // Fallback if app_type column doesn't exist yet in the live DB
        try {
            $stmt_ann = $pdo->query("SELECT * FROM announcements WHERE active = 1 ORDER BY id DESC LIMIT 1");
            $ann = $stmt_ann->fetch();
        } catch (Exception $e2) {
            $ann = null;
        }
    }

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
        $row['packageName'] = (string)($row['packageName'] ?? '');
        $row['versionCode'] = (int)$row['versionCode'];
        $row['latestVersion'] = (string)$row['latestVersion'];
        return $row;
    }, $apps_stmt->fetchAll(PDO::FETCH_ASSOC));

    $response = [
        "status" => "success",
        "config" => [
            "announcement" => $ann['message'] ?? 'Welcome!',
            "isMaintenance" => ($all_config['app_status'] ?? 'ON') === 'OFF',
            "status" => $all_config['app_status'] ?? 'ON',
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