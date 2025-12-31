<?php
// config.php - MySQL Database Configuration
$db_host = 'localhost';
$db_name = 'silentmu_app';
$db_user = 'silentmu_isam';
$db_pass = '844121@LuvKush';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If connection fails, we'll use a fallback or display an error
    // die("Connection failed: " . $e->getMessage());
}

function getConfig($key, $default = '') {
    global $pdo;
    if (!$pdo) return $default;
    $stmt = $pdo->prepare("SELECT config_value FROM app_config WHERE config_key = ?");
    $stmt->execute([$key]);
    $res = $stmt->fetch();
    return $res ? $res['config_value'] : $default;
}

function setConfig($key, $value) {
    global $pdo;
    if (!$pdo) return;
    $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
    $stmt->execute([$key, $value, $value]);
}

function getActiveAnnouncement() {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE active = 1 AND (start_time IS NULL OR start_time <= NOW()) AND (end_time IS NULL OR end_time >= NOW()) ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

function getAllPanels() {
    global $pdo;
    if (!$pdo) return [];
    $stmt = $pdo->prepare("SELECT * FROM panels WHERE enabled = 1 ORDER BY position ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}
?>