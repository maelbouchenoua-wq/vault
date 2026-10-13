-- This file is used to set up the database on your device creating all tables.

-- Create the database.
CREATE DATABASE IF NOT EXISTS vault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Table users
CREATE TABLE users
(
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)            NOT NULL,
    email      VARCHAR(80)            NOT NULL UNIQUE,
    password   VARCHAR(1024)          NOT NULL,
    role       ENUM ('user', 'admin') NOT NULL,
    best_score INT UNSIGNED           NOT NULL DEFAULT 0,
    created_at DATETIME               NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;


-- Create table categories.
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL UNIQUE
);

INSERT INTO categories (name) VALUES
    ('Catégorie 1'),
    ('Catégorie 2'),
    ('Catégorie 3');