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
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
    category_id  INT UNSIGNED NOT NULL UNIQUE,
    FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT,
    title        VARCHAR(90),
    release_year DATETIME     NOT NULL UNIQUE,
    description  VARCHAR(512) NOT NULL,
    image_path   VARCHAR(521) NOT NULL
);


-- create table user_media.
CREATE TABLE user_media
(
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id  INT UNSIGNED NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    media_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE,
    statut ENUM('planned', 'in_progress', 'completed') NOT NULL,
    rating   INT DEFAULT NULL,

    CONSTRAINT uq_user_media UNIQUE (user_id, media_id)
);


-- TABLE badges
CREATE TABLE badges
(
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(50)  NOT NULL,
    name        VARCHAR(50) NOT NULL,
    description TEXT        NOT NULL
) ENGINE = InnoDB;

INSERT INTO badges(code, name, description) VALUES
     ('collector', 'collector',
      'Posséder au moins 5 œuvres dans sa collection personnelle.'),
     ('cinephile', 'cinephile',
      'Avoir terminé le visionnage d au moins 3 films du catalogue.'),
     ('gamer','gamer',
      'Avoir terminé au moins 3 jeux vidéo répertoriés.'),
     ('explorer', 'explorer',
      'Enregistrer un score d au moins 15 au mini-jeu interactif.');

-- TABLE user_badges

CREATE TABLE user_badges
(
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    badge_id   INT UNSIGNED,
    FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE,
    awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;