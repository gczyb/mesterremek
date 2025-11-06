-- Treasure Quest Database Setup
-- Run this SQL to create the database structure

CREATE DATABASE IF NOT EXISTS treasure_quest;
USE treasure_quest;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture LONGBLOB DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- If table already exists, add/modify the profile_picture column:
-- ALTER TABLE users MODIFY COLUMN profile_picture LONGBLOB DEFAULT NULL;