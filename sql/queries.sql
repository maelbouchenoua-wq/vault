-- liste les œuvres d’un utilisateur avec leur titre
-- leur catégorie, leur statut et leur note. --

select users.id as utilisateur ,user_media.statut, user_media.rating, medias.title, categories.name as categories
FROM user_media
         INNER JOIN medias ON medias.id = user_media.media_id
         INNER JOIN users ON users.id = user_media.user_id
         INNER JOIN categories ON categories.id = medias.category_id
         where users.id = 3;