<?php
require_once __DIR__ . "/../../includes/db.php";
/**
 * This file contains all function related to media manipulation.
 */


/**
 * Insert a new `$media` in the database.
 * @param array $media The data of the media to add. THE PROVIDED MEDIA SHOULD HAVE BEEN VALIDATED AND PARSED.
 * @throws Error If the operation failed.
 */
function add_new_media(array $media): void
{
    $pdo = getPDO();
    $query = $pdo->prepare("INSERT INTO medias (category_id, title, release_year, description, image_path) VALUES (:category, :title, :release_year, :description,:image_path)");
    $query->execute([
        'category' => $media["category"],
        'title' => $media["title"],
        'release_year' => $media["release_year"],
        'description' => $media["description"],
        'image_path' => $media["cover"],
    ]);
}
