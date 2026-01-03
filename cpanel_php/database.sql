-- Silent Panel Database Schema (MySQL Compatible for cPanel)

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

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS apps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(255) NOT NULL,
    category_id INT,
    packageName VARCHAR(255) NULL,
    iconUrl VARCHAR(500) NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS app_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_id INT NOT NULL,
    version_name VARCHAR(255) NOT NULL,
    apk_url VARCHAR(500) NOT NULL,
    is_latest INT DEFAULT 0,
    version_code BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS download_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_id INT,
    version_id INT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (app_id) REFERENCES apps(id),
    FOREIGN KEY (version_id) REFERENCES app_versions(id)
);

-- Default Admin (admin123)
INSERT INTO admins (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username=username;

-- Default Config
INSERT INTO app_config (config_key, config_value) VALUES ('app_status', 'ON'), ('main_logo_url', '') ON DUPLICATE KEY UPDATE config_key=config_key;
