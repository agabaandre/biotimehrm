-- Script to make id column auto-increment in biotime_departments table
-- Run this SQL directly in your database

-- Check if id column exists and modify it to be auto-increment
ALTER TABLE biotime_departments 
MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Ensure it's the primary key (if not already)
ALTER TABLE biotime_departments 
ADD PRIMARY KEY (id);

-- Reset auto-increment counter
ALTER TABLE biotime_departments AUTO_INCREMENT = 1;

-- Verify the structure
DESCRIBE biotime_departments;


