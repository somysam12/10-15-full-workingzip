-- database.sql
-- Import this into your MySQL database using phpMyAdmin

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
    message TEXT NOT NULL,
    start_time DATETIME,
    end_time DATETIME,
    active TINYINT(1) DEFAULT 1
);

-- Default data
INSERT IGNORE INTO app_config (config_key, config_value) VALUES ('app_enabled', 'true');
INSERT IGNORE INTO app_config (config_key, config_value) VALUES ('app_title', 'Silent Multi Panel');
INSERT IGNORE INTO app_config (config_key, config_value) VALUES ('logo_url', 'logo.png');
