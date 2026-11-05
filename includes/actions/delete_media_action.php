<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../router-helper.php';
require_once __DIR__ . '/../controllers/media_controller.php';

/**
 * Action for deleting a media.
 */

# Require to be logged to a valid admin account.
requireAdmin();

$media_id = $_GET["media_id"];
if ($media_id === null) {
    redirect("/admin.php");
} else $media_id = intval($media_id);


try {
    delete_media($media_id);
    redirect_successful("/admin.php", "Nous avons bien supprimé votre média.");
} catch (Exception $error) {
    if ($error->getMessage() == "La base de donnée à renvoyé une erreur.")
        redirect_error("/admin.php", 500, $error->getMessage());
    else redirect_error("/admin.php");
}

