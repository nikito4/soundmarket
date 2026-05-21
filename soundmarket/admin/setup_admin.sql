-- ============================================================
--  SoundMarket Admin Panel — One-time setup
--  Run in phpMyAdmin or: mysql -u root soundmarket < setup_admin.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `username`      varchar(50)  NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role`          enum('superadmin','editor') NOT NULL DEFAULT 'editor',
  `created_at`    timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `admin_id`     int(11)      DEFAULT NULL,
  `action`       varchar(255) NOT NULL,
  `target_table` varchar(60)  DEFAULT NULL,
  `target_id`    int(11)      DEFAULT NULL,
  `ip_address`   varchar(45)  DEFAULT NULL,
  `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default credentials: admin / admin123  ← change after first login!
INSERT INTO `admin_users` (`username`, `password_hash`, `role`)
VALUES ('admin', '$2y$10$te3gG7QzMdXLjqddyheW5e/4Cm3bQt694T1Wes/7ps/wfzza1u9RO', 'superadmin')
ON DUPLICATE KEY UPDATE username = username;
