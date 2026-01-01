<?php
// config.php - Unified Config for Replit and CPanel
session_start();

$db_host = 'localhost';
$db_name = 'silentmu_app';
$db_user = 'silentmu_isam';
$db_pass = '844121@LuvKush';

if (getenv('DATABASE_URL')) {
    $db_url = parse_url(getenv('DATABASE_URL'));
    $db_host = $db_url['host'];
    $db_user = $db_url['user'];
    $db_pass = $db_url['pass'];
    $db_name = ltrim($db_url['path'], '/');
}

try {
    $pdo = new PDO("pgsql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        // Silently fail or log
    }
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
    $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES (?, ?) ON CONFLICT (config_key) DO UPDATE SET config_value = EXCLUDED.config_value");
    $stmt->execute([$key, $value]);
}

function isLoggedIn() { return isset($_SESSION['admin_id']); }
function requireLogin() { if (!isLoggedIn()) { header("Location: login.php"); exit(); } }

function getAllConfig() {
    global $pdo;
    if (!$pdo) return [];
    $stmt = $pdo->query("SELECT config_key, config_value FROM app_config");
    $config = [];
    while ($row = $stmt->fetch()) { $config[$row['config_key']] = $row['config_value']; }
    return $config;
}

function getAllPanels() {
    global $pdo;
    if (!$pdo) return [];
    $stmt = $pdo->query("SELECT * FROM panels ORDER BY id ASC");
    return $stmt->fetchAll();
}

function getActiveAnnouncement() {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->query("SELECT * FROM announcements WHERE active = TRUE ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}
?>