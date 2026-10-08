-- Create the database for Library Management System
CREATE DATABASE IF NOT EXISTS Library_ms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Show the created database
SHOW DATABASES LIKE 'Library_ms';

-- Use the database
USE Library_ms;

-- Show that we're using the correct database
SELECT DATABASE() as current_database;