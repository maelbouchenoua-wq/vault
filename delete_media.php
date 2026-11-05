<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/router-helper.php';
require_once __DIR__ . '/includes/controllers/media_controller.php';

# Require to be logged to a valid admin account.
requireAdmin();

$media_id = $_GET["media_id"];
if ($media_id === null) {
    redirect("/admin.php");
} else $media_id = intval($media_id);
$media = fetch_media($media_id);
if ($media === null) redirect_error("/admin.php", 400, "Ce média n'existe pas.");

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vault - Supprimer <?= $media["title"] ?></title>
</head>
<body>
<section>
    <h1>Êtes-vous sûr de vouloir supprimer <?= $media["title"] ?> ?</h1>
    <div class="button-container">
        <a href="/includes/actions/delete_media_action.php?media_id=<?= $media["id"] ?>">Supprimer</a>
        <a>Retour</a>
    </div>
</section>
</body>
</html>
