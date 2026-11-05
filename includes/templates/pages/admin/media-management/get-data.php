<?php
include_once "./includes/db.php";

/** Render all media on admin page */
function render_all_media(): void
{
    $pdo = getPDO();
    $query = $pdo->query("SELECT media.id, title, description, categories.name as category, release_year, COUNT(user_media.user_id) as collection_count, image_path FROM medias as media
                                LEFT JOIN user_media on media.id = user_media.media_id
                                JOIN vault.categories on media.category_id = categories.id
                                GROUP BY media.id
                                ORDER BY media.id;
                                ");
    while ($media = $query->fetch()) {
        include __DIR__ . "/table-row.php";
    }
}