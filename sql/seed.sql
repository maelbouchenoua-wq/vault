INSERT INTO categories (name)
VALUES ('film'),
       ('series'),
       ('game');

INSERT INTO media (category_id, title, release_year, description, image_path)
VALUES (1, 'Blade Runner 2049', 2017, 'Trente ans après les événements du premier film, un nouveau blade runner, l''officier K, déterre un secret...', 'blade_runner_cover.jpg'),
       (1, 'Dune: Deuxième Partie', 2024, 'Paul Atreides s''unit à Chani et aux Fremen tout en préparant sa revanche contre les conspirateurs qui ont...', 'dune_2_cover.jpg'),
       (1, 'Interstellar', 2014, 'Une équipe d''explorateurs franchit un trou de ver récemment découvert afin de surpasser les limites du...', 'interstellar_cover.jpg'),
       (1, '', 0000, '', ''),
       (2, '', 0000, '', ''),
       (2, '', 0000, '', ''),
       (2, '', 0000, '', ''),
       (2, '', 0000, '', ''),
       (3, '', 0000, '', ''),
       (3, '', 0000, '', ''),
       (3, '', 0000, '', ''),
       (3, '', 0000, '', '')
;



INSERT INTO badges(code, name, description)
VALUES ('collector', 'collector', 'Posséder au moins 5 œuvres dans sa collection personnelle.'),
       ('cinephile', 'cinephile', 'Avoir terminé le visionnage d au moins 3 films du catalogue.'),
       ('gamer', 'gamer', 'Avoir terminé au moins 3 jeux vidéo répertoriés.'),
       ('explorer', 'explorer', 'Enregistrer un score d au moins 15 au mini-jeu interactif.');