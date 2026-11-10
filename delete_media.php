<?php
require_once __DIR__ . '/includes/libs/auth.php';
require_once __DIR__ . '/includes/libs/router-helper.php';
require_once __DIR__ . '/includes/controllers/media_controller.php';

# Require to be logged to a valid admin account.
requireAdmin();

$media_id = $_GET["media_id"];
if ($media_id === null) {
    redirect("/admin.php");
} else $media_id = intval($media_id);

$media = fetch_media($media_id);
if ($media === null) redirect("/admin.php");
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vault - Supprimer <?= $media["title"] ?></title>
    <link rel="stylesheet" href="./assets/css/reset.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/components/header.css">
    <link rel="stylesheet" href="./assets/css/components/footer.css">
    <link rel="stylesheet" href="./assets/css/pages/delete_media.css">
    <link rel="stylesheet" href="assets/css/components/notification.css">
    <script src="./includes/js/form_action.js" defer></script>
</head>
<body>
<?php
$current_page = "delete_media";
include __DIR__ . '/includes/templates/layout/header.php';
?>
<section id="deletion_confirmation">
    <div class="container">
        <h1>Êtes-vous sûr de vouloir supprimer <?= $media["title"] ?> ?</h1>
        <p>Cette action est irreversible. <br>
            Elle supprimera le média de toutes les collections qui le contiennent.</p>
        <div class="button-container">
            <form action="/includes/actions/delete_media_action.php?media_id=<?= $media["id"] ?>" method="POST">
                <input type="submit" id="submit">
                <label for="submit">
                    <a class="danger">Supprimer</a>
                </label>
            </form>
            <a href="/admin.php">Retour</a>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/includes/components/notification_displayer/notification_component.php';
?>
<?php include __DIR__ . '/includes/templates/layout/footer.php'; ?>
</body>
</html>
