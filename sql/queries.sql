-- liste les œuvres d’un utilisateur avec leur titre
-- leur catégorie, leur statut et leur note. --

select user_media.id, user_media.status, user_media.rating, medias.title, categories.name as categories
FROM user_media
         INNER JOIN medias ON medias.id = user_media.media_id
         INNER JOIN categories ON categories.id = medias.category_id;

-- Get the list of all media of a certain USER grouped by status.
/*
For user 4, result should be
+---------+-----+
|status   |count|
+---------+-----+
|completed|3    |
|planned  |2    |
+---------+-----+
*/
SELECT user_media.status, COUNT(id) as count
FROM user_media
WHERE user_id = 4
GROUP BY user_media.status;