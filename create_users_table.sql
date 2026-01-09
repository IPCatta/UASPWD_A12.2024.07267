-- Tabel users untuk sistem login
-- Jalankan query ini di phpMyAdmin untuk membuat tabel users

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert user default (password: admin123)
-- Password di-hash menggunakan password_hash() PHP
-- Atau gunakan password plain text: 'admin123'
INSERT INTO `users` (`username`, `password`, `nama`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com', 'admin', 'aktif');

-- Catatan:
-- Password hash di atas adalah untuk password: 'password'
-- Untuk password 'admin123', gunakan salah satu cara berikut:
-- 1. Hash dengan PHP: password_hash('admin123', PASSWORD_DEFAULT)
-- 2. Atau gunakan password plain text langsung di database (tidak disarankan untuk production)

-- Contoh insert user baru dengan password plain text (untuk testing):
-- INSERT INTO `users` (`username`, `password`, `nama`, `email`, `role`, `status`) VALUES
-- ('user1', 'user123', 'User Satu', 'user1@example.com', 'user', 'aktif');

