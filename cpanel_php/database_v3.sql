-- database_v3.sql
-- Comprehensive schema for Silent Panel APK Backend

-- 1. Configuration & Core
CREATE TABLE IF NOT EXISTS app_config (
    id SERIAL PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT
);

-- 2. Authentication System
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    api_token VARCHAR(64) UNIQUE,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. APK & App Management
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS apps (
    id SERIAL PRIMARY KEY,
    package_name VARCHAR(255) UNIQUE NOT NULL,
    app_name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT REFERENCES categories(id),
    icon_url TEXT,
    min_sdk INT DEFAULT 21,
    visibility ENUM('public', 'private', 'hidden') DEFAULT 'public',
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS app_versions (
    id SERIAL PRIMARY KEY,
    app_id INT REFERENCES apps(id) ON DELETE CASCADE,
    version_name VARCHAR(50) NOT NULL,
    version_code INT NOT NULL,
    apk_url TEXT NOT NULL,
    file_size BIGINT,
    checksum_sha256 VARCHAR(64),
    changelog TEXT,
    status ENUM('active', 'beta', 'deprecated') DEFAULT 'active',
    is_latest BOOLEAN DEFAULT FALSE,
    force_update BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Panel System (Old features compatibility)
CREATE TABLE IF NOT EXISTS panels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    url TEXT NOT NULL,
    site_key VARCHAR(100) UNIQUE NOT NULL,
    position INT DEFAULT 0,
    enabled BOOLEAN DEFAULT TRUE
);

-- 5. Announcement System
CREATE TABLE IF NOT EXISTS announcements (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255),
    message TEXT NOT NULL,
    button_text VARCHAR(100),
    button_link TEXT,
    priority ENUM('normal', 'warning', 'urgent') DEFAULT 'normal',
    type ENUM('banner', 'popup') DEFAULT 'banner',
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Analytics & Logs
CREATE TABLE IF NOT EXISTS download_stats (
    id SERIAL PRIMARY KEY,
    app_id INT REFERENCES apps(id),
    version_id INT REFERENCES app_versions(id),
    ip_address VARCHAR(45),
    user_agent TEXT,
    country_code CHAR(2),
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS device_logs (
    id SERIAL PRIMARY KEY,
    device_model VARCHAR(100),
    android_version VARCHAR(20),
    app_version VARCHAR(50),
    package_name VARCHAR(255),
    ip_address VARCHAR(45),
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_logs (
    id SERIAL PRIMARY KEY,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    ip_address VARCHAR(45),
    status_code INT,
    request_time FLOAT,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Setup Data
INSERT INTO app_config (config_key, config_value) VALUES 
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
('splash_text_color', '#ffffff'),
('splash_text_position', 'center'),
('bg_color', '#0A0E27')
ON CONFLICT (config_key) DO NOTHING;

-- Default Admin (password: admin123)
INSERT INTO admins (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON CONFLICT (username) DO NOTHING;
