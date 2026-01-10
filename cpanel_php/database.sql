-- Silent Panel Database Schema (MySQL Optimized)

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS app_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(255) UNIQUE NOT NULL,
    config_value TEXT
);

CREATE TABLE IF NOT EXISTS license_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    device_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    message TEXT,
    button_text VARCHAR(255),
    button_link VARCHAR(500),
    type VARCHAR(50) DEFAULT 'banner',
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    active TINYINT(1) DEFAULT 0,
    app_type VARCHAR(50) NOT NULL DEFAULT 'all'
);

CREATE TABLE IF NOT EXISTS panels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500),
    site_key VARCHAR(255),
    package_name VARCHAR(255),
    version VARCHAR(50) DEFAULT '1.0.0'
);

CREATE TABLE IF NOT EXISTS apps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(255) NOT NULL,
    category_id INT,
    packageName VARCHAR(255) NULL,
    iconUrl VARCHAR(500) NULL
);

-- Default Config
INSERT INTO app_config (config_key, config_value) VALUES
('maintenance_enabled', 'false'),
('maintenance_message', 'App under maintenance. Please come back later.'),
('announcement_enabled', 'true'),
('announcement_title', 'New Update'),
('announcement_message', 'Server upgraded. Enjoy smoother experience!'),
('announcement_type', 'info'),
('login_required', 'true'),
('login_logo_url', ''),
('layout_preset', 'RIGHT_FOCUS'),
('viewport_app_scale', '1.25'),
('viewport_shift_right_dp', '120'),
('viewport_shift_down_dp', '120'),
('viewport_black_left_dp', '50'),
('viewport_container_width_percent', '92'),
('viewport_container_height_percent', '100'),
('crop_auto_detect_banner', 'true'),
('crop_min_banner_height_px', '5'),
('css_enable', 'true'),
('css_zoom_scale', '1.5'),
('css_hide_selectors', 'header,.top-banner,.banner,.vmos-header'),
('modes_focus_mode', 'true'),
('modes_lock_reveal', 'true')
ON DUPLICATE KEY UPDATE config_key=config_key;
