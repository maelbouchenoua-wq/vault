<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

$user = currentUser();


// Vérifie si une note a été envoyée.
if (isset($_POST['rating'])) {

    // L'utilisateur doit être connecté.
    if ($user === null) {
        header('Location: ' . authUrl('login.php'));
        exit;
    }

    // Récupère les informations envoyées par le formulaire.
    $mediaId = filter_input(INPUT_POST, 'media_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

    // Vérifie que les deux valeurs sont correctes.
    if (
        $mediaId !== false && $mediaId !== null && $mediaId > 0 &&
        $rating !== false && $rating !== null && $rating >= 1 && $rating <= 5
    ) {
        $pdo = getPDO();

        // Vérifie que le média appartient à la collection de l'utilisateur.
        $query = $pdo->prepare(
            "SELECT id FROM user_media
             WHERE user_id = :user_id AND media_id = :media_id"
        );

        $query->execute([
            'user_id' => $user['id'],
            'media_id' => $mediaId
        ]);

        $existingMedia = $query->fetch();

        if ($existingMedia) {

            // Enregistre la nouvelle note.
            $query = $pdo->prepare(
                "UPDATE user_media
                 SET rating = :rating
                 WHERE user_id = :user_id AND media_id = :media_id"
            );

            $query->execute([
                'rating' => $rating,
                'user_id' => $user['id'],
                'media_id' => $mediaId
            ]);

        }
    }

    // Recharge le catalogue après le traitement.
    header('Location: /index.php');
    exit;
}
