<?php

function add_media_to_collection(int $user_id, int $media_id): string
{
    $pdo = getPDO();

    // Vérifie si le média est déjà dans la collection.
    $query = $pdo->prepare(
        "SELECT id FROM user_media
             WHERE user_id = :user_id AND media_id = :media_id"
    );

    $query->execute([
        'user_id' => $user_id,
        'media_id' => $media_id
    ]);

    $existingMedia = $query->fetch();

    // Ajoute le média uniquement s'il n'existe pas déjà.
    if (!$existingMedia) {
        $query = $pdo->prepare(
            "INSERT INTO user_media (user_id, media_id, status, rating)
         VALUES (:user_id, :media_id, 'planned', NULL)"
        );

        $query->execute([
            'user_id' => $user_id,
            'media_id' => $media_id
        ]);

        return "success";
    } else {
        return "exists";
    }
}

