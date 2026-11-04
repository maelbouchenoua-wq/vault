<?php
/**
 * This file contains all form validator functions.
 */


const ACCEPTED_FILE_TYPE = ["image/jpeg", "image/jpeg", "image/webp"];

/**
 * Return if the provided `$value` is a valid string :
 * - At least 1 char after trim.
 * - Under `$max_length` char long after trim.
 */
function is_string_valid($value, int $max_length = 200): bool
{
    return is_string($value) && strlen(trim($value)) >= 1 && strlen(trim($value)) <= $max_length;
}

/**
 * Returns if the provided `$value` is a string or `null`.
 * @see is_string_valid() For on information for string validation.
 */
function is_optional_string_valid($value, int $max_length = 200): bool
{
    return $value === null || (is_string($value) && strlen($value) == 0) || is_string_valid($value, $max_length);
}


/**
 * Returns if the provided `$value` is a valid year int:
 * - Later than 1900.
 * - Before than in five years.
 */
function is_year_valid($value): bool
{
    return is_int(intval($value)) && intval($value) > 1900 and intval($value) < date("Y") + 5;
}

/** Returns if the file at the provided `$file` is a valid image. */
function is_image_attachement_valid($file): bool
{
    return $file !== null
        && in_array($file["type"], ACCEPTED_FILE_TYPE)
        // 5 MB
        && $file["size"] <= 5 * 1024 * 1024
        && getimagesize($file["tmp_name"]);
}

/** Returns if the `$value` is an empty file */
function is_attachement_empty($value): bool {
    return $value === null || strlen($value["full_path"]) == 0;
}