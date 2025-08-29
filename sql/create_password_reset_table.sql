-- Create password reset tokens table for forgot password functionality
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add last_login column to user table if it doesn't exist
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `last_login` datetime NULL AFTER `status`;

-- Add index for better performance
CREATE INDEX IF NOT EXISTS `idx_user_username_email` ON `user` (`username`, `email`);
CREATE INDEX IF NOT EXISTS `idx_user_last_login` ON `user` (`last_login`);
