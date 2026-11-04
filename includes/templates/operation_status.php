<?php

/**
 * Parse the return `$code` to a displayable text.
 */
function parse_error_code(int $code): string
{
    return match ($code) {
        400 => "Les données que vous avez renseignées sont incorrectes : ",
        401 => "Vous n'avez pas les authorizations necessaire pour effectuer cette action.",
        default => "Une erreur interne est survenue : "
    };
}

/**
 * Render an HTML 'operation_status' element with the content of the URL
 */
function render_operation_status(): void
{
    if ($_GET["status"] === null) return;
    $status_code = intval(urldecode($_GET["status"]));
    $status = $status_code <= 300 ? "ok" : "error";
    if ($status == "ok") {
        $message = urldecode($_GET["message"]);
    } else $message = urldecode($_GET["error"]);
    if (strlen($message) == 0) {
        $message = urldecode($_GET["message"]) ?? ("Opération " . ($status == "ok" ? "réussie" : "échoué") . ".");
    }

    $title = $status == "error" ? parse_error_code($status_code) . "<br>" : "";
    echo "
    <div class='operation_status $status'>
        <h3>$title $message</h3>
    </div>";
}
