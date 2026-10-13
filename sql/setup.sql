# Ce ficher sert a recréer la base de donnée sur votre machine.
-- This file is used to set up the database on your device creating all tables.

-- Create the database.
CREATE DATABASE IF NOT EXISTS vault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE vault;
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
CREATE TABLE categories
(
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL UNIQUE
);

INSERT INTO categories (name)
VALUES ('Catégorie 1'),
       ('Catégorie 2'),
       ('Catégorie 3');

-- create table media.
CREATE TABLE media
(
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categorie    VARCHAR(120) NOT NULL UNIQUE,
    title        VARCHAR(90),
    release_year DATETIME     NOT NULL UNIQUE
);

-- create table user_media.
CREATE TABLE user_media
(
    id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT         NOT NULL,
    media_id INT         NOT NULL,
    status   VARCHAR(50) NOT NULL,
    rating   INT DEFAULT NULL,

    CONSTRAINT uq_user_media UNIQUE (user_id, media_id)
);

-- TABLE badges

CREATE TABLE badges
(
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        INT UNIQUE  NOT NULL,
    name        VARCHAR(50) NOT NULL,
    description TEXT        NOT NULL
) ENGINE = InnoDB;

-- TABLE user_badges

CREATE TABLE user_badges
(
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT,
    FOREIGN KEY (user_id) REFERENCES users (id),
    badge_id   INT,
    FOREIGN KEY (badge_id) REFERENCES badges (id),
    awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;