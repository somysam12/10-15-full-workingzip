<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : 'config';

try {
    $db = get_db_connection();

    if ($action === 'config') {
        // Fetch app_enabled
        $stmt = $db->prepare("SELECT `value` FROM app_config WHERE `key` = 'app_enabled'");
        $stmt->execute();
        $enabled = $stmt->fetchColumn();
        $enabled = ($enabled === 'true');

        // Fetch app_title
        $stmt = $db->prepare("SELECT `value` FROM app_config WHERE `key` = 'app_title'");
        $stmt->execute();
        $app_title = $stmt->fetchColumn() ?: 'Silent Multi Panel';

        // Fetch logo_url
        $stmt = $db->prepare("SELECT `value` FROM app_config WHERE `key` = 'logo_url'");
        $stmt->execute();
        $logo_url = $stmt->fetchColumn() ?: '';

        // Fetch active announcement
        $stmt = $db->prepare("SELECT message FROM announcements WHERE active = 1 ORDER BY created_at DESC LIMIT 1");
        $stmt->execute();
        $announcement = $stmt->fetchColumn() ?: '';

        // Fetch panels
        $stmt = $db->prepare("SELECT name, url, site_key FROM panels WHERE enabled = 1 ORDER BY position ASC");
        $stmt->execute();
        $panels = $stmt->fetchAll();

        echo json_encode([
            'enabled' => $enabled,
            'announcement' => $announcement,
            'logo_url' => $logo_url,
            'app_title' => $app_title,
            'panels' => $panels
        ]);
    } 
    // Admin API endpoints
    elseif ($action === 'add_panel') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("INSERT INTO panels (name, url, site_key, position) VALUES (?, ?, ?, (SELECT COALESCE(MAX(position), 0) + 1 FROM (SELECT position FROM panels) as p))");
        $stmt->execute([$data['name'], $data['url'], $data['site_key']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_panel') {
        $site_key = $_GET['site_key'];
        $stmt = $db->prepare("UPDATE panels SET enabled = 0 WHERE site_key = ?");
        $stmt->execute([$site_key]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'add_announcement') {
        $data = json_decode(file_get_contents('php://input'), true);
        $db->exec("UPDATE announcements SET active = 0");
        $stmt = $db->prepare("INSERT INTO announcements (message, active) VALUES (?, ?)");
        $stmt->execute([$data['message'], $data['active'] ? 1 : 0]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'update_title') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("UPDATE app_config SET `value` = ? WHERE `key` = 'app_title'");
        $stmt->execute([$data['title']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'update_logo') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("UPDATE app_config SET `value` = ? WHERE `key` = 'logo_url'");
        $stmt->execute([$data['url']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'toggle_server') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("UPDATE app_config SET `value` = ? WHERE `key` = 'app_enabled'");
        $stmt->execute([$data['enabled'] ? 'true' : 'false']);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>