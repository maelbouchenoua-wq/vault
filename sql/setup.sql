# Ce ficher sert a recréer la base de donnée sur votre machine.
-- This file is used to set up the database on your device creating all tables.

-- Create the database.
CREATE DATABASE IF NOT EXISTS vault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE media(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categorie VARCHAR(120)NOT NULL UNIQUE,
    title VARCHAR(90),
    release_year DATETIME  NOT NULL UNIQUE
    )

