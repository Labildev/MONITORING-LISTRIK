CREATE DATABASE IF NOT EXISTS izazmonitor;
USE izazmonitor;

CREATE TABLE IF NOT EXISTS energy_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tegangan FLOAT DEFAULT 0,
    arus FLOAT DEFAULT 0,
    daya FLOAT DEFAULT 0,
    energi FLOAT DEFAULT 0,
    relay1 TINYINT(1) DEFAULT 0,
    relay2 TINYINT(1) DEFAULT 0,
    relay3 TINYINT(1) DEFAULT 0,
    relay4 TINYINT(1) DEFAULT 0,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password is 'password' hashed with bcrypt)
INSERT IGNORE INTO users (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default relay names
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('relay1_name', 'Beban 1'),
('relay2_name', 'Beban 2'),
('relay3_name', 'Beban 3'),
('relay4_name', 'Beban 4');
