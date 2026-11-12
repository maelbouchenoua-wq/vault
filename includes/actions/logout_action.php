<?php

require_once __DIR__ . '/../libs/auth.php';
startSession();

/* Accepte uniquement une déconnexion demandée en POST. */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('La déconnexion doit être envoyée depuis le bouton Se déconnecter.');
}

/* Refuse les demandes sans jeton CSRF valide. */
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('La session du formulaire a expiré. Actualisez la page puis réessayez.');
}

/* Vide la session et suprime son cookie. */
$_SESSION = [];
$parameters = session_get_cookie_params();

setcookie(session_name(), '', [
    'expires' => time() - 42000,
    'path' => $parameters['path'],
    'domain' => $parameters['domain'],
    'secure' => $parameters['secure'],
    'httponly' => $parameters['httponly'],
    'samesite' => $parameters['samesite'] ?? 'Lax',
]);

session_destroy();

header('Location: ' . authUrl('login.php?logged_out=1'), true, 303);
exit;
