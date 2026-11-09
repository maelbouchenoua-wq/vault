<?php
require_once __DIR__ . '/../libs/db.php';
/**
 * This file contains all function related to media categories.
 */

/**
 * Collect all media categories from the database.
 * @internal
 */
function _fetch_categories()
{
    $pdo = getPDO();

    $query = $pdo->query('SELECT * FROM categories ORDER BY id');
    $results = $query->fetchAll();

    if (count($results) == 0) {
        exit("Error: Cannot fetch media categories from the database.");
    }
    return $results;
}

/**
 * Returns the list of all categories
 *
 * Uses an internal catch to avoir re-fetch at every call.
 */
function get_categories_list(): ?array
{
    static $categories = null;

    if ($categories === null) {
        $categories = _fetch_categories();
    }
    return $categories;
}

/**
 * Parse a `$category` name to a text to display.
 * @param string $category The name of the category.
 * @return string The text to display.
 */
function get_display_category(string $category): string
{
    return match ($category) {
        'film' => "Film",
        'game' => "Jeu video",
        'series' => "Série"
    };
}

/**
 * Returns if the provided `$value` is a valid category.
 */
function is_category_valid(string $value): bool
{
    $categories = [];
    foreach (get_categories_list() as $cat) {
        $categories[] = $cat["id"];
    }
    return in_array($value, $categories);
}
