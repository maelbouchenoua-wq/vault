<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../router-helper.php';
require_once __DIR__ . '/../validator.php';
require_once __DIR__ . '/../controllers/media_controller.php';
require_once __DIR__ . '/../categories_manager.php';

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
    redirect_error("/admin.php", 400, "ValidationError: Title is not properly formatted");
    return;
}

# Category
$category = $_POST["category"];
if (is_string_valid($category, 1000)) {
    if (is_valid_category($category)) {
        $data["category"] = $category;
    } else {
        redirect_error("/admin.php", 400, "ValidationError: Category is not valid");
        return;
    }
} else {
    redirect_error("/admin.php", 400, "ValidationError: Category is not properly formatted");
    return;
}

# Release year.
$release_year = $_POST["release_year"];
if (is_year_valid($release_year)) {
    $data["release_year"] = intval($release_year);
} else {
    redirect_error("/admin.php", 400, "L'année de publication doit être entre 1900 et " . date("Y") + 5 . ".");
    return;
}

# Cover
$cover = $_POST["cover"];
if (is_image_attachement_valid($cover)) {
    $data["cover"] = $cover;
} else {
    redirect_error("/admin.php", 400, "L'image n'est pas valide.");
    return;
}

# Description
$description = $_POST["description"];
if (is_optional_string_valid($description, 2000)) {
    $data["description"] = $description;
} else {
    redirect_error("/admin.php", 400, "La description devrait faire moins de 2000 caractères.");
    return;
}

# -- Add the media
try {
    add_new_media($data);
    redirect_successful("/admin.php", "Nous avons bien ajouté votre média.");
} catch (Error $error) {
    echo $error;
    redirect_error("/admin.php");
}
