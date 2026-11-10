<?php

/**
 * Redirect the current request to the provided `$location`.
 */
function redirect(string $location): void
{
    header('Location: ' . $location);
}