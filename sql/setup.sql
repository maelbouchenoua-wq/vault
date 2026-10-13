-- This file is used to set up the database on your device creating all tables.

-- Create the database.
CREATE DATABASE IF NOT EXISTS vault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


USE vault;
-- TABLE badges

CREATE TABLE badges(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code INT UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL
) ENGINE = InnoDB;

-- TABLE user_badges

CREATE TABLE user_badges(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user INT UNSIGNED REFERENCES id users,
    id_badge INT UNSIGNED REFERENCES id badges,
    awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;