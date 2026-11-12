<?php
require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../controllers/collection_controller.php';
requireLogin();

$format = $_GET['format'] ?? '';
if (!is_string($format) || !in_array($format, ['json', 'csv'], true)) {
    http_response_code(400);
    exit('Choisissez le format JSON ou CSV.');
}

try {
    $user = currentUser();
    $collection = getProfileCollection($user['id']);
} catch (PDOException $exception) {
    http_response_code(503);
    exit('L’export de la collection est momentanément indisponible.');
}

// Prépare les colonnes du fichier avec les œuvres de ce compte.
$rows = [];
foreach ($collection['items'] as $item) {
    $rows[] = [
        'media_id' => $item['media_id'],
        'title' => $item['title'],
        'category' => $item['category_label'],
        'release_year' => $item['release_year'],
        'status' => $item['status'],
        'rating' => $item['rating'],
        'xp' => $item['xp']
    ];
}

header('X-Content-Type-Options: nosniff');
header('Content-Disposition: attachment; filename="vault-collection.' . $format . '"');

if ($format === 'json') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

header('Content-Type: text/csv; charset=UTF-8');
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // Garde les accents à l'ouverture dans un tableur.
fputcsv($output, ['media_id', 'title', 'category', 'release_year', 'status', 'rating', 'xp'], ';', '"', '');

foreach ($rows as $row) {
    foreach ($row as $column => $value) {
        // Empêche qu'un titre soit interprété comme une formule de tableur.
        if (is_string($value) && preg_match('/^\s*[=+\-@]/u', $value)) {
            $row[$column] = "'" . $value;
        }
    }
    fputcsv($output, $row, ';', '"', '');
}

fclose($output);
