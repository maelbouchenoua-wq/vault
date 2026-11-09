<?php

// Échappe une valeur pour son affichage dans le HTML.
function catalogueHtml($value): string
{
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Uniformise les catégories reconnues pour les compteurs et styles.
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

// Conserve un statut reconnu, sinon utilise le statut planned.
function catalogueStatus($status): string
{
    return in_array($status, ['planned', 'in_progress', 'completed'], true) ? $status : 'planned';
}

// Traduit le statut normalisé en libellé francais.
function getStatusText($status): string
{
    return match (catalogueStatus($status)) {
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
        default => 'À découvrir'
    };
}

// Construit le badge HTML et sa classe CSS.
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

// Complète la note avec cinq étoiles pleines ou vides.
function renderStarsNotation($starCount, int $mediaId): string
{
    $starCount = is_numeric($starCount)
        ? max(0, min(5, (int) $starCount))
        : 0;

    $content = "<div class='star_container'>";

    for ($i = 1; $i <= 5; $i++) {
        $image = $i <= $starCount
            ? '/assets/images/star_filled.svg'
            : '/assets/images/star_empty.svg';

        $content .= "
            <button
                type='submit'
                form='rating-form-$mediaId'
                name='rating'
                value='$i'
                class='star-button'
                aria-label='Donner une note de $i sur 5'
            >
                <img src='$image' alt=''>
            </button>
        ";
    }

    $content .= "</div>";

    return $content;
}



// Assemble une fiche avec les données déjà sélectionnées.
function renderCatalogueCard(array $media): string
{
    $tag = renderCardStatusTag($media['status'] ?? 'planned');
    $mediaId = (int) ($media['id'] ?? 0);
    $stars = renderStarsNotation($media['rating'] ?? null, $mediaId);
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
    $mediaId = (int) ($media['id'] ?? 0);

    $imagePath = is_string($media['image_path'] ?? null) ? trim($media['image_path']) : '';
    $imageFile = basename(str_replace('\\', '/', $imagePath));

    // Choisit l'image générique lorsque le nom est inexploitable.
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
                        <form method='POST' id='rating-form-$mediaId' action='/includes/actions/set_rating_action.php'>
                            <input type='hidden' name='media_id' value='$mediaId'>
                            $stars
                        </form>
                    </div>
                    <div class='status'>
                        <p class='state'>Statut: $status</p>
                        <div class='state_action'>
                             <form method='POST' action='/includes/actions/add_media_to_collection_action.php'>
                                 <input type='hidden' name='media_id' value='$mediaId'>
                                 <button type='submit' name='add_media'>
                                     Add
                                 </button>
                             </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
}
