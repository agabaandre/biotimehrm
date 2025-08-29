-- Create activity_log table for tracking user activities
CREATE TABLE IF NOT EXISTS `activity_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `module` varchar(255) DEFAULT NULL,
  `route` varchar(100) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `fk_user_id` (`fk_user_id`),
  KEY `module` (`module`),
  KEY `created_at` (`created_at`),
  KEY `route` (`route`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add foreign key constraint if user table exists
-- ALTER TABLE `activity_log` ADD CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`fk_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

-- Insert some sample data for testing
INSERT INTO `activity_log` (`fk_user_id`, `activity`, `module`, `route`, `ip_address`, `created_at`) VALUES
(1, 'User logged in successfully', 'auth', 'auth/login', '127.0.0.1', NOW()),
(1, 'Viewed dashboard', 'dashboard', 'dashboard/index', '127.0.0.1', NOW()),
(1, 'Viewed facilities management', 'lists', 'lists/getFacilities', '127.0.0.1', NOW());
