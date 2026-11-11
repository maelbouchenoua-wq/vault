<?php
require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../controllers/collection_controller.php';
requireLogin();

$user = currentUser();
$userId = $user['id'];

// Le formulaire doit être envoyé en POST avec le bon jeton.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . authUrl('profile.php#collection'), true, 303);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Le formulaire a expiré. Revenez au profil et réessayez.');
}

$action = $_POST['action'] ?? '';
$actionsPossibles = ['add', 'update', 'remove'];
if (!is_string($action) || !in_array($action, $actionsPossibles, true)) {
    http_response_code(400);
    exit('Action de collection inconnue.');
}

// Garde le message en session, puis revient sur le profil.
function collectionRedirect($type, $message, $query = '', $form = [])
{
    $_SESSION['collection_flash'] = [
        'type' => $type,
        'message' => $message,
        'form' => $form
    ];

    $anchor = '#collection';
    if ($query !== '') {
        $anchor = '#collection-editor';
    }

    header('Location: ' . authUrl('profile.php' . $query . $anchor), true, 303);
    exit;
}

$entryId = false;
if (isset($_POST['entry_id']) && is_string($_POST['entry_id'])) {
    $entryId = filter_var($_POST['entry_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

$queryString = '?collection=add';
if ($action !== 'add') {
    $queryString = '?collection=edit&entry_id=' . (int) $entryId;
}

// Permet de retrouver les valeurs du formulaire après une erreur.
$form = [];
foreach (['media_id', 'status', 'rating'] as $field) {
    $form[$field] = '';
    if (isset($_POST[$field]) && is_string($_POST[$field])) {
        $form[$field] = substr($_POST[$field], 0, 32);
    }
}

try {
    $pdo = getPDO(true);

    // On ne peut modifier que les œuvres du compte connecté.
    if ($action !== 'add') {
        if ($entryId === false) {
            http_response_code(404);
            exit('Cette œuvre ne fait pas partie de votre collection.');
        }

        $query = $pdo->prepare('SELECT id FROM user_media WHERE id = :entry_id AND user_id = :user_id');
        $query->execute(['entry_id' => $entryId, 'user_id' => $userId]);

        if ($query->fetchColumn() === false) {
            http_response_code(404);
            exit('Cette œuvre ne fait pas partie de votre collection.');
        }
    }

    if ($action === 'remove') {
        $query = $pdo->prepare('DELETE FROM user_media WHERE id = :entry_id AND user_id = :user_id');
        $query->execute(['entry_id' => $entryId, 'user_id' => $userId]);
        collectionRedirect('success', 'L’œuvre a été retirée de votre collection.');
    }

    $status = $_POST['status'] ?? '';
    $statutsPossibles = ['planned', 'in_progress', 'completed'];
    if (!is_string($status) || !in_array($status, $statutsPossibles, true)) {
        collectionRedirect('error', 'Choisissez un statut valide.', $queryString, $form);
    }

    $ratingInput = $_POST['rating'] ?? '';
    $notesPossibles = ['', '1', '2', '3', '4', '5'];
    if (!is_string($ratingInput) || !in_array($ratingInput, $notesPossibles, true)) {
        collectionRedirect('error', 'La note doit être comprise entre 1 et 5, ou laissée vide.', $queryString, $form);
    }

    $rating = null;
    if ($ratingInput !== '') {
        $rating = (int) $ratingInput;
    }

    if ($action === 'add') {
        $mediaId = false;
        if (isset($_POST['media_id']) && is_string($_POST['media_id'])) {
            $mediaId = filter_var($_POST['media_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        }
        if ($mediaId === false) {
            collectionRedirect('error', 'Choisissez une œuvre du catalogue.', $queryString, $form);
        }

        $query = $pdo->prepare('SELECT id FROM medias WHERE id = :media_id');
        $query->execute(['media_id' => $mediaId]);
        if ($query->fetchColumn() === false) {
            collectionRedirect('error', 'Choisissez une œuvre du catalogue.', $queryString, $form);
        }

        $query = $pdo->prepare('SELECT id FROM user_media WHERE user_id = :user_id AND media_id = :media_id');
        $query->execute(['user_id' => $userId, 'media_id' => $mediaId]);
        if ($query->fetchColumn() !== false) {
            collectionRedirect('error', 'Cette œuvre est déjà dans votre collection.', $queryString, $form);
        }

        $query = $pdo->prepare(
            'INSERT INTO user_media (user_id, media_id, status, rating)
             VALUES (:user_id, :media_id, :status, :rating)'
        );
        $query->execute([
            'user_id' => $userId,
            'media_id' => $mediaId,
            'status' => $status,
            'rating' => $rating
        ]);
        collectionRedirect('success', 'L’œuvre a été ajoutée à votre collection.');
    }

    $query = $pdo->prepare(
        'UPDATE user_media SET status = :status, rating = :rating
         WHERE id = :entry_id AND user_id = :user_id'
    );
    $query->execute([
        'status' => $status,
        'rating' => $rating,
        'entry_id' => $entryId,
        'user_id' => $userId
    ]);
    collectionRedirect('success', 'Le statut et la note ont été enregistrés.');
} catch (PDOException $exception) {
    $message = 'La collection est momentanément indisponible. Réessayez plus tard.';

    // La base bloque aussi deux ajouts simultanés de la même œuvre.
    if ($exception->getCode() === '23000') {
        $codeErreur = (int) ($exception->errorInfo[1] ?? 0);
        if ($codeErreur === 1062 || str_contains($exception->getMessage(), 'UNIQUE constraint failed')) {
            $message = 'Cette œuvre est déjà dans votre collection.';
        }
    }

    collectionRedirect('error', $message, $queryString, $form);
}
