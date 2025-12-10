-- SQL Script to create settings table
-- Run this in phpMyAdmin or MySQL CLI

CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(255) NOT NULL UNIQUE,
  value TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default values (optional)
INSERT IGNORE INTO settings (`key`, `value`) VALUES
('footer_address', 'Jakarta, Indonesia'),
('footer_email', 'info@jsmuguard.com'),
('footer_phone', ''),
('footer_copyright', '© 2025 JSMU Guard. All rights reserved.');
