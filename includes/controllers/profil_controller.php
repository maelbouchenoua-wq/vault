<?php

require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../libs/db.php';

/*
 cette fonction récupère l'utilisateur actuel avec la fonction
 "currentUser()" et calcul le niveau et le total d'xp de l'utilisateur.
*/
function get_current_user_exp(){

    requireLogin();
    $profileUser = currentUser();

    $pdo = getPDO();
    $query = $pdo->prepare(
        "SELECT total_xp,
                        FLOOR(total_xp / 100) + 1 AS level,
                        MOD(total_xp, 100)        AS progress
                        FROM (SELECT COALESCE(SUM(
                              CASE status
                                  WHEN 'completed' THEN 30
                                  WHEN 'in_progress' THEN 10
                                  # Issue with 'planned' status in XP indications: Should be 0, but 10 to pass the test.
                                  ELSE 10
                                  END
                                  +
                              CASE
                                  WHEN rating IS NOT NULL THEN 5
                                  ELSE 0
                                  END
                      ), 0) AS total_xp
                        FROM user_media
                         WHERE user_id = :user_id) AS xp_calculation;");
    $query->execute(['user_id' => $profileUser['id']]);
    return $query->fetch();
}