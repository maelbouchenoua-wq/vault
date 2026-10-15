-- liste les œuvres d’un utilisateur avec leur titre
-- leur catégorie, leur statut et leur note. --

select users.id as utilisateur ,user_media.status, user_media.rating, medias.title, categories.name as categories
FROM user_media
         INNER JOIN medias ON medias.id = user_media.media_id
         INNER JOIN users ON users.id = user_media.user_id
         INNER JOIN categories ON categories.id = medias.category_id
where users_id = 3;

-- moyenne request --
    SELECT
    CASE
        WHEN COUNT(rating) = 0 THEN 'Absence de moyenne'
        ELSE CAST(ROUND(AVG(rating), 2) AS CHAR)
    END AS moyenne
FROM user_media INNER JOIN users ON users.id = user_media.user_id
where user_id = 1;


-- calcule des XP d’un utilisateur une collection vide donne 0 et le jeu de test principal donne 135 --

SELECT
    COALESCE (SUM(
            CASE status
                WHEN 'completed' THEN 30
                WHEN 'in_progress' THEN 10
                ELSE 0
            END
            +
            CASE
                WHEN note IS NOT NULL THEN 5
                ELSE 0
            END
    ), 0) AS total_xp
FROM user_media
WHERE user_id = 1;




-- Calcule du niveau et de la progression à partir des XP --

SELECT 
    total_xp,
    FLOOR(total_xp / 100) + 1 AS level,
    MOD(total_xp, 100) AS progress
FROM (
    SELECT
        COALESCE (SUM(
                CASE status
                    WHEN 'completed' THEN 30
                    WHEN 'in_progress' THEN 10
                    ELSE 0
                END
                +
                CASE
                    WHEN note IS NOT NULL THEN 5
                    ELSE 0
                END
        ), 0) AS total_xp
    FROM user_media
    WHERE user_id = 1
) AS xp_calculation;