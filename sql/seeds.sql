INSERT INTO categories (name)
VALUES ('film'),
       ('series'),
       ('game');

INSERT INTO medias (category_id, title, release_year, description, image_path)
VALUES (1, 'Blade Runner 2049', 2017, 'Trente ans après les événements du premier film, un nouveau blade runner, l''officier K, déterre un secret...', 'blade_runner_cover.jpg'),
       (1, 'Dune: Deuxième Partie', 2024, 'Paul Atreides s''unit à Chani et aux Fremen tout en préparant sa revanche contre les conspirateurs qui ont...', 'dune_2_cover.jpg'),
       (1, 'Interstellar', 2014, 'Une équipe d''explorateurs franchit un trou de ver récemment découvert afin de surpasser les limites du...', 'interstellar_cover.jpg'),
       (1, 'Avatar', 2009,
        'Avatar (2009) raconte l''histoire de Jake Sully, un ancien marine envoyé sur la planète Pandora. Il découvre le peuple Na''vi et leur monde fascinant, ce qui va bouleverser sa vie et le pousser à choisir entre deux camps.',
        'avatar_cover.jpg'),
       (2, 'Breaking Bad', 2008, 'Un professeur de chimie se transforme en fabricant de méthamphétamine pour subvenir aux besoins de sa famille.', 'breaking_bad_cover.jpg'),
       (2, 'Game of Thrones', 2011, 'Des nobles families se battent pour le contrôle du Trône de Fer dans un monde fictif.', 'game_of_thrones_cover.jpg'),
       (2, 'Stranger Things', 2016, 'Des enfants découvrent un mystère surnaturel dans leur petite ville.', 'stranger_things_cover.jpg'),
       (2, 'The Crown', 2016, 'L''histoire de la reine Elizabeth II et de sa famille au cours du XXe siècle.', 'the_crown_cover.jpg'),
       (3, 'The Legend of Zelda: Breath of the Wild', 2017, 'Link explore le royaume d''Hyrule dans ce jeu d''aventure en monde ouvert.', 'zelda_botw_cover.jpg'),
       (3, 'The Witcher 3: Wild Hunt', 2015, 'Geralt de Rivia voyage à travers un monde en pleine guerre à la recherche de sa fille adoptive.', 'witcher_3_cover.jpg'),
       (3, 'The Elder Scrolls V: Skyrim', 2011, 'Joueur incarne un chevalier dans le monde de Tamriel, où il peut choisir son chemin et ses alliances.', 'skyrim_cover.jpg'),
       (3, 'Red Dead Redemption 2', 2018, 'Un cow-boy dans le Far West à la fin du XIXe siècle, confronté aux changements de société.', 'rdr2_cover.jpg')
;


INSERT INTO users (username, email, password, role)
VALUES ('Bob', 'bob@gmail.com', 'D769087D8F0A1658960207D90C196A2DEED7455AE93455C53A7F073743BDA41A', 'user'),
       ('Pat', 'patOch_@proton.com', '45EB55D6F3B79B270C13BF4E48309DAB03EBC73D414AFFC52F7EE7514849F3E3', 'admin'),
       ('Max', 'maximax_malveillance_max@yahoo.com', 'BCBBEDB20803C55455FBF48F0EBF1044DE2E4B5DA33C99AFDD0A01F35052C7F4', 'user'),
       ('Jame', 'james@gmail.com', 'E46240714B5DB3A23EEE60479A623EFBA4D633D27FE4F03C904B9E219A7FBE60', 'user')
;


INSERT INTO badges(code, name, description)
VALUES (1001, 'collector', 'Posséder au moins 5 œuvres dans sa collection personnelle.'),
       (1002, 'cinephile', 'Avoir terminé le visionnage d au moins 3 films du catalogue.'),
       (1003, 'gamer', 'Avoir terminé au moins 3 jeux vidéo répertoriés.'),
       (1004, 'explorer', 'Enregistrer un score d au moins 15 au mini-jeu interactif.');

INSERT INTO user_media(user_id, media_id, status, rating)
VALUES (1,1,'planned', null),
       (2,2,'in_progress', null),
       (3,3,'completed', 2),
       (3,2,'in_progress', null),
       (4,1,'completed', 5),
       (4,2,'completed', 4),
       (4,3,'completed', 2),
       (4,4,'planned', 1),
       (4,5,'planned', 4);