<?php
require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../libs/db.php';
require_once __DIR__ . '/../controllers/collection_controller.php';
require_once __DIR__ . '/../libs/router-helper.php';

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

        $result = add_media_to_collection($user["id"], $mediaId);
        $_SESSION['message'] = $result;
    }

    // Recharge la page après le traitement.
    redirect("/index.php");
    exit;
}
