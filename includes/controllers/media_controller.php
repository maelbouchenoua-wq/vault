<?php
require_once __DIR__ . "/../../includes/db.php";
/**
 * This file contains all function related to media manipulation.
 */

const ATTACHEMENT_FOLDER_PATH = __DIR__ . "/../../assets/images/content";


/**
 * Insert a new `$media` in the database.
 * @param array $media The data of the media to add. THE PROVIDED MEDIA SHOULD HAVE BEEN VALIDATED AND PARSED.
 * @throws Error If the operation failed.
 * @throws Exception
 */
function add_new_media(array $media): void
{
    $pdo = getPDO();
    # Add attachement
    if ($media["cover"] !== null) {
        $file_path = _download_attachement($media["cover"]);
    }

    # Add to database
    $query = $pdo->prepare("INSERT INTO medias (category_id, title, release_year, description, image_path) VALUES (:category, :title, :release_year, :description,:image_path)");
    try {
        $query->execute([
            'category' => $media["category"],
            'title' => $media["title"],
            'release_year' => $media["release_year"],
            'description' => $media["description"],
            'image_path' => $file_path ?? null,
        ]);
    } catch (PDOException) {
        throw new Error("La base de donnée à renvoyé une erreur.");
    }
}


function add_media_to_collection(int $user_id, int $media_id): void
{
    $pdo = getPDO();

    $query = $pdo->prepare(
        "SELECT id FROM user_media
         WHERE user_id = :user_id AND media_id = :media_id"
    );

    $query->execute([
        'user_id' => $user_id,
        'media_id' => $media_id
    ]);

    $media = $query->fetch();

    if ($media) {
        return;
    }

    $query = $pdo->prepare(
        "INSERT INTO user_media (user_id, media_id, status, rating)
         VALUES (:user_id, :media_id, 'planned', NULL)"
    );

    $query->execute([
        'user_id' => $user_id,
        'media_id' => $media_id
    ]);
}


/**
 * Insert the `$file` in the attachement directory.
 * @internal
 */
function _download_attachement($file): string
{
    $file_name = strtolower($file["name"]);
    $path = ATTACHEMENT_FOLDER_PATH . "/" . $file_name;
    move_uploaded_file($file["tmp_name"], $path);
    return $file_name;
}