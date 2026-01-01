<?php
// config.php - Enhanced Database Configuration with Auth Support
session_start();

// Database credentials
$db_host = 'localhost';
$db_name = 'silentmu_app';
$db_user = 'silentmu_isam';
$db_pass = '844121@LuvKush';

// Try to use environment variables (Replit standard) if available
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
    // If PG fails, try MySQL for legacy compatibility if needed
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        // die("Connection failed: " . $e2->getMessage());
    }
}

// --- Configuration Helpers ---

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
    // Postgres compatible upsert
    $stmt = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES (?, ?) ON CONFLICT (config_key) DO UPDATE SET config_value = EXCLUDED.config_value");
    $stmt->execute([$key, $value]);
}

// --- Auth Helpers ---

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// --- Core Data Helpers ---

function getActiveAnnouncement() {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE active = TRUE AND (start_time IS NULL OR start_time <= CURRENT_TIMESTAMP) AND (end_time IS NULL OR end_time >= CURRENT_TIMESTAMP) ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

function getAllConfig() {
    global $pdo;
    if (!$pdo) return [];
    $stmt = $pdo->query("SELECT config_key, config_value FROM app_config");
    $rows = $stmt->fetchAll();
    $config = [];
    foreach ($rows as $row) {
        $config[$row['config_key']] = $row['config_value'];
    }
    return $config;
}

function getAllPanels() {
    global $pdo;
    if (!$pdo) return [];
    $stmt = $pdo->query("SELECT * FROM panels WHERE enabled = TRUE ORDER BY position ASC");
    return $stmt->fetchAll();
}

// --- API Security Helpers ---

function validateApiToken() {
    $headers = getallheaders();
    $token = $headers['X-API-Token'] ?? $_GET['token'] ?? null;
    if (!$token) return false;
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE api_token = ?");
    $stmt->execute([$token]);
    return (bool)$stmt->fetch();
}

function logApiRequest($endpoint, $status = 200) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO api_logs (endpoint, method, ip_address, status_code) VALUES (?, ?, ?, ?)");
    $stmt->execute([$endpoint, $_SERVER['REQUEST_METHOD'], $_SERVER['REMOTE_ADDR'], $status]);
}
?>