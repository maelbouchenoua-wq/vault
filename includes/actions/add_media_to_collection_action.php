<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

$user = currentUser();

// Vérifie si le formulaire Add a été envoyé.
if (isset($_POST['add_media'])) {

    // Vérifie que l'utilisateur est connecté.
    if ($user === null) {
        header('Location: ' . authUrl('login.php'));
        exit;
    }

    // Récupère l'identifiant du média envoyé par le formulaire.
    $mediaId = filter_input(INPUT_POST, 'media_id', FILTER_VALIDATE_INT);

    if ($mediaId !== false && $mediaId !== null && $mediaId > 0) {

        $pdo = getPDO();

        // Vérifie si le média est déjà dans la collection.
        $query = $pdo->prepare(
            "SELECT id FROM user_media
             WHERE user_id = :user_id AND media_id = :media_id"
        );

        $query->execute([
            'user_id' => $user['id'],
            'media_id' => $mediaId
        ]);

        $existingMedia = $query->fetch();

        // Ajoute le média uniquement s'il n'existe pas déjà.
        if (!$existingMedia) {
            $query = $pdo->prepare(
                "INSERT INTO user_media (user_id, media_id, status, rating)
         VALUES (:user_id, :media_id, 'planned', NULL)"
            );

            $query->execute([
                'user_id' => $user['id'],
                'media_id' => $mediaId
            ]);

            $_SESSION['message'] = "success";
        } else {
            $_SESSION['message'] = "exists";
        }
    }

    // Recharge la page après le traitement.
    header('Location: /index.php');
    exit;
}
