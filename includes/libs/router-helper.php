<?php

/**
 * Redirect the current request to the provided `$location`.
 * @deprecated Should be replaced by functions from `action-helper` with response code.
 */
function redirect(string $location): void
{
    header('Location: ' . $location);
}