<?php
require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../libs/validator.php';
require_once __DIR__ . '/action-helper.php';
require_once __DIR__ . '/../controllers/media_controller.php';
require_once __DIR__ . '/../controllers/categories_controller.php';

/**
 * Action for creating a new media.
 */

# Require to be logged to a valid admin account.
requireAdmin();

# -- Validate and parse data
$data = [];

# Title
$title = $_POST["title"];
if (is_string_valid($title, 150)) {
    $data["title"] = $title;
} else {
    respond_error(400, "ValidationError: Title is not properly formatted");
    return;
}

# Category
$category = $_POST["category"];
if (is_string_valid($category, 1000)) {
    if (is_category_valid($category)) {
        $data["category"] = $category;
    } else {
        respond_error(400, "ValidationError: Category is not valid");
        return;
    }
} else {
    respond_error(400, "ValidationError: Category is not properly formatted");
    return;
}

# Release year.
$release_year = $_POST["release_year"];
if (is_year_valid($release_year)) {
    $data["release_year"] = intval($release_year);
} else {
    respond_error(400, "L'année de publication doit être entre 1900 et " . date("Y") + 5 . ".");
    return;
}

# Cover
$cover = $_FILES["cover"];
if (is_attachement_empty($cover)) $data["cover"] = null;
else if (is_image_attachement_valid($cover)) {
    $data["cover"] = $cover;
} else {
    respond_error(400, "L'image n'est pas valide.");
    return;
}

# Description
$description = $_POST["description"];
if (is_optional_string_valid($description, 2000)) {
    $data["description"] = strlen($description) == 0 ? null : $description;
} else {
    respond_error(400, "La description devrait faire moins de 2000 caractères.");
    return;
}

# -- Add the media
try {
    $media = add_new_media($data);
    ob_start();
    include(__DIR__ . '/../templates/pages/admin/media-management-section/table-row.php');
    $render = ob_get_clean();
    respond_success_with_content([
        'message' => "Nous avons bien ajouté votre média.",
        'new_row' => (string)$render
    ]);
} catch (Exception $error) {
    if ($error->getMessage() == "La base de donnée à renvoyé une erreur.")
        respond_error(500, $error->getMessage());
    else respond_error();
}
