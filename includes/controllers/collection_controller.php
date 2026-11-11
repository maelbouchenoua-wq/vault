<?php
require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../libs/db.php';

// Échappe les valeurs destinées au HTML.
function collectionHtml(mixed $value): string
{
    $text = '';
    if (is_scalar($value)) {
        $text = (string) $value;
    }

    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Reconnaît les catégories habituelles et conserve les autres libellés.
function collectionCategory(mixed $value): array
{
    $name = '';
    if (is_string($value)) {
        $name = trim($value);
    }

    if (function_exists('mb_strtolower')) {
        $key = mb_strtolower($name, 'UTF-8');
    } else {
        $key = strtolower($name);
    }
    $key = strtr($key, ['É' => 'e', 'é' => 'e', 'È' => 'e', 'è' => 'e', 'Ê' => 'e', 'ê' => 'e']);
    $key = preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $key));

    $filmNames = ['film', 'films', 'movie', 'movies'];
    $seriesNames = ['serie', 'series', 'tv series'];
    $gameNames = ['game', 'games', 'jeu', 'jeux', 'jeu video', 'jeux video',
        'video game', 'video games', 'videogame', 'videogames'];

    if (in_array($key, $filmNames, true)) {
        return ['key' => 'film', 'label' => 'Film'];
    } elseif (in_array($key, $seriesNames, true)) {
        return ['key' => 'series', 'label' => 'Série'];
    } elseif (in_array($key, $gameNames, true)) {
        return ['key' => 'game', 'label' => 'Jeu vidéo'];
    }

    if ($name === '') {
        $name = 'Autre';
    }

    return ['key' => 'other', 'label' => $name];
}

// Une ancienne note hors de 1 à 5 reste non notée.
function collectionRating(mixed $value): ?int
{
    if (is_string($value)) {
        if (preg_match('/^[1-5]$/D', $value)) {
            return (int) $value;
        }
    } elseif (is_int($value)) {
        if ($value >= 1 && $value <= 5) {
            return $value;
        }
    }

    return null;
}

// Utilise une affiche locale existante ou l’image générique.
function collectionImageUrl(mixed $value): string
{
    $path = '';
    if (is_string($value)) {
        $path = trim($value);
    }
    $filename = basename(str_replace('\\', '/', $path));

    if ($filename === '' || $filename === '.' || $filename === '..') {
        return authUrl('assets/images/media.png');
    }
    if (str_contains($filename, "\0")) {
        return authUrl('assets/images/media.png');
    }

    $imageFile = dirname(__DIR__, 2) . '/assets/images/content/' . $filename;
    if (!is_file($imageFile)) {
        return authUrl('assets/images/media.png');
    }

    return authUrl('assets/images/content/' . rawurlencode($filename));
}

// Charge la collection du compte et calcule ses statistiques.
function getProfileCollection(int $userId): array
{
    if ($userId <= 0) {
        throw new InvalidArgumentException('Identifiant de compte invalide.');
    }

    $pdo = getPDO(true);
    $query = $pdo->prepare(
        'SELECT user_media.id, user_media.media_id, user_media.status, user_media.rating,
                medias.title, medias.release_year, medias.description, medias.image_path,
                categories.name AS category_name
         FROM user_media
         INNER JOIN medias ON medias.id = user_media.media_id
         INNER JOIN categories ON categories.id = medias.category_id
         WHERE user_media.user_id = :user_id
         ORDER BY medias.title ASC, medias.id ASC'
    );
    $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $query->execute();

    $items = [];
    $stats = [
        'count' => 0,
        'completed_films' => 0,
        'series_count' => 0,
        'rated_count' => 0,
        'average' => null,
        'total_xp' => 0,
        'completed_count' => 0,
        'level' => 1,
        'progress' => 0,
        'remaining' => 100,
    ];
    $ratingTotal = 0;

    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $category = collectionCategory($row['category_name']);
        $status = 'planned';
        if (in_array($row['status'], ['planned', 'in_progress', 'completed'], true)) {
            $status = $row['status'];
        }

        $statusLabel = 'À découvrir';
        $statusClass = 'to_discover';
        if ($status === 'completed') {
            $statusLabel = 'Terminé';
            $statusClass = 'finished';
        } elseif ($status === 'in_progress') {
            $statusLabel = 'En cours';
            $statusClass = 'ongoing';
        }

        $rating = collectionRating($row['rating']);

        // Chaque œuvre rapporte 10 XP, puis deux bonus possibles.
        $xp = 10;
        if ($status === 'completed') {
            $xp += 20;
        }
        if ($rating !== null) {
            $xp += 5;
        }

        $items[] = [
            'id' => (int) $row['id'],
            'media_id' => (int) $row['media_id'],
            'title' => (string) ($row['title'] ?? ''),
            'release_year' => (int) $row['release_year'],
            'description' => (string) ($row['description'] ?? ''),
            'category_name' => (string) $row['category_name'],
            'category_key' => $category['key'],
            'category_label' => $category['label'],
            'image_url' => collectionImageUrl($row['image_path']),
            'status' => $status,
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'rating' => $rating,
            'xp' => $xp,
        ];

        $stats['count']++;
        $stats['total_xp'] += $xp;
        if ($status === 'completed') {
            $stats['completed_count']++;
            if ($category['key'] === 'film') {
                $stats['completed_films']++;
            }
        }
        if ($category['key'] === 'series') {
            $stats['series_count']++;
        }
        if ($rating !== null) {
            $stats['rated_count']++;
            $ratingTotal += $rating;
        }
    }

    if ($stats['rated_count'] > 0) {
        $stats['average'] = (float) $ratingTotal / $stats['rated_count'];
    }
    $stats['level'] = intdiv($stats['total_xp'], 100) + 1;
    $stats['progress'] = $stats['total_xp'] % 100;
    $stats['remaining'] = 100 - $stats['progress'];

    return ['items' => $items, 'stats' => $stats];
}

// Propose uniquement les médias absents de cette collection.
function getCollectionChoices(int $userId): array
{
    if ($userId <= 0) {
        throw new InvalidArgumentException('Identifiant de compte invalide.');
    }

    $pdo = getPDO(true);
    $query = $pdo->prepare(
        'SELECT medias.id AS media_id, medias.title, medias.release_year, categories.name AS category_name
         FROM medias
         INNER JOIN categories ON categories.id = medias.category_id
         WHERE NOT EXISTS (
             SELECT 1 FROM user_media
             WHERE user_media.media_id = medias.id AND user_media.user_id = :user_id
         )
         ORDER BY medias.title ASC, medias.id ASC'
    );
    $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $query->execute();

    $choices = [];
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $category = collectionCategory($row['category_name']);
        $choices[] = [
            'media_id' => (int) $row['media_id'],
            'title' => (string) ($row['title'] ?? ''),
            'category_label' => $category['label'],
            'release_year' => (int) $row['release_year'],
        ];
    }

    return $choices;
}

function add_media_to_collection(int $user_id, int $media_id): string
{
    $pdo = getPDO();

    // Vérifie si le média est déjà dans la collection.
    $query = $pdo->prepare(
        "SELECT id FROM user_media
             WHERE user_id = :user_id AND media_id = :media_id"
    );

    $query->execute([
        'user_id' => $user_id,
        'media_id' => $media_id
    ]);

    $existingMedia = $query->fetch();

    // Ajoute le média uniquement s'il n'existe pas déjà.
    if (!$existingMedia) {
        $query = $pdo->prepare(
            "INSERT INTO user_media (user_id, media_id, status, rating)
         VALUES (:user_id, :media_id, 'planned', NULL)"
        );

        $query->execute([
            'user_id' => $user_id,
            'media_id' => $media_id
        ]);

        return "success";
    } else {
        return "exists";
    }
}

