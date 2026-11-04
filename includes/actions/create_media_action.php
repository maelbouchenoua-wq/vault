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
    echo "ValidationError: Title is not properly formatted";
    return;
}

# Category
$category = $_POST["category"];
if (is_string_valid($category, 1000)) {
    if (is_valid_category($category)) {
        $data["category"] = $category;
    } else {
        echo "ValidationError: Category is not valid";
        return;
    }
} else {
    echo "ValidationError: Category is not properly formatted";
    return;
}

# Release year.
$release_year = $_POST["release_year"];
if (is_year_valid($release_year)) {
    $data["release_year"] = intval($release_year);
} else {
    echo "ValidationError: Year is not properly formatted";
    return;
}

# Status
//TODO: WARN: No initial_status
/*$status = $_POST["status"];
if (is_string_valid($status, 1000)) {

} else {
    echo "ValidationError: Status is not properly formatted";
    return;
}*/


# Cover
$cover = $_POST["cover"];
if (is_image_attachement_valid($cover)) {
    $data["cover"] = $cover;
} else {
    echo 'ValidationError: Cover image is not properly formatted';
    return;
}

# Description
$description = $_POST["description"];
if (is_optional_string_valid($description, 2000)) {
    $data["description"] = $description;
} else {
    echo "ValidationError: Description is not properly formatted";
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
