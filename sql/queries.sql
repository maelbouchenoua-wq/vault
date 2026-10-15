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

-- Badge eligibility requests
-- Badge 'collector' - Returns all users that 'Have at least 5 media with a status.' and doesn't already have this badge.
SELECT user_media.user_id, COUNT(*) as collection_count
FROM user_media
# Get if the user has the badge
WHERE EXISTS(SELECT user_badges.badge_id FROM user_badges WHERE user_badges.user_id = user_media.user_id AND badge_id = 1) = 0
GROUP BY user_media.user_id
HAVING COUNT(*) >= 5;

-- Badge 'cinephile' - Returns all users that 'Have finished watching at least 3 movies.' and doesn't already have this badge.
SELECT user_id, COUNT(user_media.id) AS movies_count
FROM user_media
         JOIN medias ON user_media.media_id = medias.id
WHERE category_id = 1
  AND status = 'completed'
  AND EXISTS (SELECT user_badges.badge_id
              FROM user_badges
              WHERE user_badges.user_id = user_media.user_id
                AND badge_id = 2) = 0
GROUP BY user_media.user_id
HAVING COUNT(*) >= 3;

-- Badge 'gamer' - Returns all users that 'Have finished at least 3 video games'.
SELECT user_id, COUNT(user_media.id) AS movies_count
FROM user_media
         JOIN medias ON user_media.media_id = medias.id
WHERE category_id = 3
  AND status = 'completed'
  AND EXISTS (SELECT user_badges.badge_id
              FROM user_badges
              WHERE user_badges.user_id = user_media.user_id
                AND badge_id = 2) = 0
GROUP BY user_media.user_id
HAVING COUNT(*) >= 3;


-- Badge 'explorer' - Returns all users that 'Have achieved at least 15 to the hidden mini-game'
SELECT users.id, best_score
FROM users
WHERE best_score >= 15
GROUP BY users.id;


-- Badge update requests
-- Add badge 'collector'
INSERT INTO user_badges (user_id, badge_id)
## Sub request Badge eligibility
SELECT user_media.user_id, (1) as badge_id
FROM user_media
# Get if the user has the badge
WHERE EXISTS(SELECT user_badges.badge_id FROM user_badges WHERE user_badges.user_id = user_media.user_id AND badge_id = 1) = 0
GROUP BY user_media.user_id
HAVING COUNT(*) >= 5;

-- Add badge 'cinephile'
INSERT INTO user_badges (user_id, badge_id)
SELECT user_id, (2) AS badge_id
FROM user_media
         JOIN medias ON user_media.media_id = medias.id
WHERE category_id = 1
  AND status = 'completed'
  AND EXISTS (SELECT user_badges.badge_id
              FROM user_badges
              WHERE user_badges.user_id = user_media.user_id
                AND badge_id = 2) = 0
GROUP BY user_media.user_id
HAVING COUNT(*) >= 3;

-- Add badge 'gamer'
INSERT INTO user_badges (user_id, badge_id)
SELECT user_id, (3) as badge_id
FROM user_media
         JOIN medias ON user_media.media_id = medias.id
WHERE category_id = 3
  AND status = 'completed'
  AND EXISTS (SELECT user_badges.badge_id
              FROM user_badges
              WHERE user_badges.user_id = user_media.user_id
                AND badge_id = 2) = 0
GROUP BY user_media.user_id
HAVING COUNT(*) >= 3;

-- Badge 'explorer' - Returns all users that 'Have achieved at least 15 to the hidden mini-game'
INSERT INTO user_badges (user_id, badge_id)
SELECT users.id, (4) AS badge_id
FROM users
WHERE best_score >= 15
GROUP BY users.id;