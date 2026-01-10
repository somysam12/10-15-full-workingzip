<?php
// config.php - Unified Config for Replit and CPanel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CRITICAL DATABASE CREDENTIALS ---
$db_host = 'localhost';
$db_name = 'shaitaanhh_controlpanel';
$db_user = 'shaitaanhh_isam';
$db_pass = '844121@LuvKush';

// --- CONNECTION LOGIC ---
if (getenv('DATABASE_URL')) {
    // Replit / Postgres Environment
    $db_url = parse_url(getenv('DATABASE_URL'));
    $db_host = $db_url['host'];
    $db_user = $db_url['user'];
    $db_pass = $db_url['pass'];
    $db_name = ltrim($db_url['path'], '/');
    $dsn = "pgsql:host=$db_host;dbname=$db_name";
} else {
    // cPanel / MySQL Environment
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Output error for debugging
    header('Content-Type: text/plain');
    die("Database Connection Error: " . $e->getMessage());
}

// Set global timezone for the entire application to India
date_default_timezone_set('Asia/Kolkata');

// --- CORE UTILITIES ---
function getConfig($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT config_value FROM app_config WHERE config_key = ?");
        $stmt->execute([$key]);
        $res = $stmt->fetch();
        return $res ? $res['config_value'] : $default;
    } catch (Exception $e) { return $default; }
}

function setConfig($key, $value) {
    global $pdo;
    try {
        $is_postgres = (getenv('DATABASE_URL')) ? true : false;
        if ($is_postgres) {
            $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES (?, ?) ON CONFLICT (config_key) DO UPDATE SET config_value = EXCLUDED.config_value");
        } else {
            $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)");
        }
        $stmt->execute([$key, $value]);
    } catch (Exception $e) {}
}

function isLoggedIn() { return isset($_SESSION['admin_id']); }
function requireLogin() { if (!isLoggedIn()) { header("Location: login.php"); exit(); } }

function getAllConfig() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT config_key, config_value FROM app_config");
        $config = [];
        while ($row = $stmt->fetch()) { $config[$row['config_key']] = $row['config_value']; }
        return $config;
    } catch (Exception $e) { return []; }
}

function getAllPanels() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM panels ORDER BY id ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) { return []; }
}

function getActiveAnnouncement() {
    global $pdo;
    if (!$pdo) return null;
    $app_type = $_SESSION['app_type'] ?? 'all';
    
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE active = 1 AND (app_type = ? OR app_type = 'all') ORDER BY id DESC LIMIT 1");
    $stmt->execute([$app_type]);
    $ann = $stmt->fetch();
    
    return [
        "enabled" => $ann ? true : false,
        "title" => $ann['title'] ?? "",
        "message" => $ann['message'] ?? "",
        "type" => $ann['type'] ?? "info"
    ];
}

function getMaintenanceConfig() {
    global $pdo;
    $app_type = $_SESSION['app_type'] ?? 'master';
    
    // Check global app status first
    $global_status = getConfig('app_status', 'ON');
    if ($global_status === 'OFF') {
        return [
            "enabled" => true,
            "message" => getConfig('maintenance_message', 'System is under maintenance.')
        ];
    }

    $status_key = ($app_type === 'panel') ? 'panel_maintenance' : 'master_maintenance';
    $msg_key = ($app_type === 'panel') ? 'panel_maintenance_msg' : 'master_maintenance_msg';
    
    $status = getConfig($status_key, 'OFF');
    $message = getConfig($msg_key, 'System is under maintenance.');
    
    return [
        "enabled" => ($status === 'ON'),
        "message" => $message
    ];
}
?>