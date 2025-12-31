-- database_v2.sql
-- Import this into your MySQL database via phpMyAdmin

CREATE TABLE IF NOT EXISTS app_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT
);

CREATE TABLE IF NOT EXISTS panels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    url TEXT NOT NULL,
    site_key VARCHAR(100) UNIQUE NOT NULL,
    position INT DEFAULT 0,
    enabled TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    message TEXT NOT NULL,
    button_text VARCHAR(100),
    button_link TEXT,
    type ENUM('banner', 'popup') DEFAULT 'banner',
    start_time DATETIME,
    end_time DATETIME,
    active TINYINT(1) DEFAULT 1
);

-- Initial Setup Data
INSERT IGNORE INTO app_config (config_key, config_value) VALUES 
('app_status', 'ON'),
('maintenance_message', 'App is under maintenance'),
('force_logout_flag', 'no'),
('latest_version', '1.0.0'),
('min_required_version', '1'),
('update_url', ''),
('update_message', 'New update available!'),
('reset_cache_flag', 'no'),
('reset_time', ''),
('theme_mode', 'System'),
('theme_locked', 'no'),
('splash_logo_url', 'logo.png'),
('loader_url', ''),
('splash_text', 'Welcome'),
('bg_color', '#0A0E27');
