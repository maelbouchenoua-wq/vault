<?php
require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../libs/router-helper.php';
require_once __DIR__ . '/../controllers/media_controller.php';
require_once __DIR__ . '/action-helper.php';

/**
 * Action for deleting a media.
 */

# Require to be logged to a valid admin account.
requireAdmin();

$media_id = $_GET["media_id"];
if ($media_id === null) {
    respond_error(400, "No media id was provided.");
} else $media_id = intval($media_id);


try {
    delete_media($media_id);
    redirect("/admin.php");
} catch (Exception $error) {
    if ($error->getMessage() == "La base de donnée à renvoyé une erreur.")
        respond_error(500, $error->getMessage());
    else respond_error();
}
