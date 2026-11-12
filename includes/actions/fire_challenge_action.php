<?php
require_once __DIR__ . '/../controllers/fire_challenge_controller.php';
startSession();
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['ok' => false, 'code' => 'method_not_allowed', 'message' => 'Envoyez une requête POST.']);
    exit;
}

$contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
if ($contentType !== 'application/json') {
    http_response_code(415);
    echo json_encode(['ok' => false, 'code' => 'invalid_content_type', 'message' => 'Envoyez les données au format JSON.']);
    exit;
}
$body = file_get_contents('php://input', false, null, 0, 8193);
$input = json_decode($body, true);
if (strlen($body) > 8192 || json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'code' => 'invalid_json', 'message' => 'Les données envoyées sont invalides.']);
    exit;
}
if (!verifyCsrfToken($input['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'code' => 'invalid_csrf', 'message' => 'Le formulaire a expiré. Rechargez la page.']);
    exit;
}
$action = $input['action'] ?? null;
if (!is_string($action)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'code' => 'invalid_action', 'message' => 'Action du défi inconnue.']);
    exit;
}

try {
    $result = fireChallengeHandle($action, $input, currentUser());
    http_response_code($result['status']);
    echo json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (PDOException $exception) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'code' => 'database_unavailable', 'message' => 'Le score ne peut pas être enregistré pour le moment. Réessayez.']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'code' => 'server_error', 'message' => 'Le défi est momentanément indisponible.']);

}
