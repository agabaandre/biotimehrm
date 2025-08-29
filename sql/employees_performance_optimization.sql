-- Database indexes for employees performance optimization
-- Run this script to improve query performance on the employees pages

-- Indexes for ihrisdata table (main employees table)
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_facility_id` ON `ihrisdata` (`facility_id`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_job_id` ON `ihrisdata` (`job_id`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_district` ON `ihrisdata` (`district`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_department_id` ON `ihrisdata` (`department_id`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_ihris_pid` ON `ihrisdata` (`ihris_pid`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_surname_firstname` ON `ihrisdata` (`surname`, `firstname`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_nin` ON `ihrisdata` (`nin`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_card_number` ON `ihrisdata` (`card_number`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_is_incharge` ON `ihrisdata` (`is_incharge`);

-- Composite indexes for better filter performance
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_district_facility` ON `ihrisdata` (`district`, `facility_id`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_district_job` ON `ihrisdata` (`district`, `job_id`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_facility_job` ON `ihrisdata` (`facility_id`, `job_id`);
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_district_facility_job` ON `ihrisdata` (`district`, `facility_id`, `job_id`);

-- Indexes for search functionality
CREATE INDEX IF NOT EXISTS `idx_ihrisdata_search` ON `ihrisdata` (`surname`, `firstname`, `othername`, `job`, `facility`, `department`);

-- Indexes for user table (for incharge assignments)
CREATE INDEX IF NOT EXISTS `idx_user_username` ON `user` (`username`);
CREATE INDEX IF NOT EXISTS `idx_user_facility_id` ON `user` (`facility_id`);
CREATE INDEX IF NOT EXISTS `idx_user_role` ON `user` (`role`);

-- Indexes for user_facilities table
CREATE INDEX IF NOT EXISTS `idx_user_facilities_user_id` ON `user_facilities` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_user_facilities_facility_id` ON `user_facilities` (`facility_id`);

-- Indexes for password_reset_tokens table
CREATE INDEX IF NOT EXISTS `idx_password_reset_tokens_user_id` ON `password_reset_tokens` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_password_reset_tokens_token` ON `password_reset_tokens` (`token`);
CREATE INDEX IF NOT EXISTS `idx_password_reset_tokens_expires_at` ON `password_reset_tokens` (`expires_at`);

-- Analyze tables for better query planning
ANALYZE TABLE `ihrisdata`;
ANALYZE TABLE `user`;
ANALYZE TABLE `user_facilities`;
ANALYZE TABLE `password_reset_tokens`;

-- Show current indexes for verification
SHOW INDEX FROM `ihrisdata`;
SHOW INDEX FROM `user`;
SHOW INDEX FROM `user_facilities`;
SHOW INDEX FROM `password_reset_tokens`;
