-- liste les œuvres d’un utilisateur avec leur titre
-- leur catégorie, leur statut et leur note. --

select user_media.id, user_media.status, user_media.rating, medias.title, categories.name as categories
FROM user_media
         INNER JOIN medias ON medias.id = user_media.media_id
         INNER JOIN categories ON categories.id = medias.category_id;