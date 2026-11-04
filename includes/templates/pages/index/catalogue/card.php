<?php

/** Escape a catalogue value for use in HTML text or attributes. */
function catalogueHtml($value): string
{
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Match catalogue categories to the existing filter counters and tag styles. */
function catalogueCategoryKey($category): ?string
{
    $category = is_string($category) ? strtolower(trim($category)) : '';

    return match ($category) {
        'film', 'films', 'movie', 'movies' => 'film',
        'serie', 'series', 'série', 'séries' => 'series',
        'game', 'games', 'jeu', 'jeux' => 'game',
        default => null
    };
}

/** Keep missing or unexpected statuses safe to render. */
function catalogueStatus($status): string
{
    return in_array($status, ['planned', 'in_progress', 'completed'], true) ? $status : 'planned';
}

/** Return the display value of a status. */
function getStatusText($status): string
{
    return match (catalogueStatus($status)) {
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
        default => 'À découvrir'
    };
}

/** Render the status tag of a card. */
function renderCardStatusTag($tag = 'planned'): string
{
    $class = match (catalogueStatus($tag)) {
        'in_progress' => 'ongoing',
        'completed' => 'finished',
        default => 'discover'
    };
    $text = catalogueHtml(strtoupper(getStatusText($tag)));

    return "<div class='element_status $class'>
                <div class='dot'></div>
                <p>$text</p>
            </div>";
}

/** Render exactly five stars, even when the stored rating is missing or invalid. */
function renderStarsNotation($starCount): string
{
    $starCount = is_numeric($starCount) ? max(0, min(5, (int) $starCount)) : 0;
    $content = '';
    for ($i = 1; $i <= $starCount; $i++) {
        $content .= "<img src='/assets/images/star_filled.svg' alt='Étoile de notation $i/5'>";
    }
    for (; $i <= 5; $i++) {
        $content .= "<img src='/assets/images/star_empty.svg' alt='Étoile de notation $i/5'>";
    }

    return $content;
}

/** Generate an HTML card using the catalogue and the current user's own data. */
function renderCatalogueCard(array $media): string
{
    $tag = renderCardStatusTag($media['status'] ?? 'planned');
    $stars = renderStarsNotation($media['rating'] ?? null);
    $status = catalogueHtml(getStatusText($media['status'] ?? 'planned'));
    $category = catalogueHtml($media['categories'] ?? '');
    $categoryClass = match (catalogueCategoryKey($media['categories'] ?? null)) {
        'series' => 'serie',
        'game' => 'game',
        default => 'movie'
    };
    $title = catalogueHtml($media['title'] ?? '');
    $description = catalogueHtml($media['description'] ?? '');
    $year = catalogueHtml($media['release_year'] ?? '');
    $imagePath = is_string($media['image_path'] ?? null) ? trim($media['image_path']) : '';
    $imageFile = basename(str_replace('\\', '/', $imagePath));
    $imageUrl = $imageFile === '' || $imageFile === '.' || $imageFile === '..'
        ? authUrl('assets/images/media.png')
        : authUrl('assets/images/content/' . rawurlencode($imageFile));
    $imageUrl = catalogueHtml($imageUrl);

    return "<div class='card'>
            <div class='header'>
                <p class='tag $categoryClass'>$category</p>
                $tag
            </div>
            <div class='overlay_container'>
                <img class='cover' src='$imageUrl' alt='Image de couverture de $title'>
            </div>
            <div class='footer'>
                <p class='element_infos'>$year</p>
                <div class='information_container'>
                    <h2>$title</h2>
                    <p class='description'>$description</p>
                </div>
                <div class='status_container'>
                    <div class='note'>
                        <p>EVALUATION :</p>
                        <div class='star_container'>$stars</div>
                    </div>
                    <div class='status'>
                        <p class='state'>Statut: $status</p>
                        <div class='state_action'>
                            <!-- Personal actions will be implemented separately. -->
                        </div>
                    </div>
                </div>
            </div>
        </div>";
}
