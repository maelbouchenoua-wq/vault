<?php

/**
 * Redirect the current request to the provided `$location`.
 */
function redirect(string $location): void
{
    header('Location: ' . $location);
}

/**
 * Redirect the current request to the provided `$location` with an 'ok' status and a custom `$message`.
 */
function redirect_successful(string $location, string $message = ''): void
{
    $path = $location . "?status=200" . ($message !== null ? "&message=" . rawurlencode($message) : "");
    header('Location: ' . $path);
}

/**
 * Redirect the current request to the provided `$location` with an 'error' status.
 * @param int $error_code The custom HTTP code of the error.
 * @param string $error The error message to transfer BE CAUTIOUS NOT TO PROVIDE THE DIRECT ERROR FOR SECURITY REASONS.
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status.
 */
function redirect_error(string $location, int $error_code = 500, string $error = ''): void
{
    $path = $location . "?status=" . rawurlencode($error_code) . ($error !== null ? "&error=" . rawurlencode($error) : "");
    header('Location: ' . $path);
}