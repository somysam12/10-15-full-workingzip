<?php
// Database configuration
// Replace these with your cPanel MySQL database details
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'your_database_name');

/**
 * Database connection helper
 */
function get_db_connection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

/**
 * Initialize database tables
 * Run this once or create tables manually in phpMyAdmin
 */
function init_db() {
    $db = get_db_connection();
    
    // app_config table
    $db->exec("CREATE TABLE IF NOT EXISTS app_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        `key` VARCHAR(100) UNIQUE NOT NULL,
        `value` TEXT
    )");
    
    // panels table
    $db->exec("CREATE TABLE IF NOT EXISTS panels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        url TEXT NOT NULL,
        site_key VARCHAR(100) UNIQUE NOT NULL,
        position INT DEFAULT 0,
        enabled BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // announcements table
    $db->exec("CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default values if missing
    $stmt = $db->prepare("INSERT IGNORE INTO app_config (`key`, `value`) VALUES ('app_enabled', 'true'), ('app_title', 'Silent Multi Panel'), ('logo_url', '')");
    $stmt->execute();
}
?>