<?php
// Prépare les données et l'affichage du catalogue.

require_once dirname(__DIR__, 4) . '/libs/db.php';
require_once __DIR__ . '/card.php';

/**
 * Render the catalogue
 * @param int|null $userId Render the whole catalogue if not provided
 * @return void
 */
function getCatalogue(?int $userId = null): void
{
    $pdo = getPDO();

    $query = $pdo->prepare(
        "SELECT
        medias.id AS id,
        user_media.user_id AS utilisateur,
        user_media.status,
        user_media.rating,
        medias.title,
        medias.image_path,
        medias.release_year,
        medias.description,
        categories.name AS categories
    FROM medias
    LEFT JOIN user_media
        ON user_media.media_id = medias.id
        AND user_media.user_id = :user_id
    INNER JOIN categories
        ON categories.id = medias.category_id
    ORDER BY medias.id"
    );

    $query->bindValue(
        ':user_id',
        $userId,
        $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT
    );

    $query->execute();

    $content = '';
    $categories_count = [
        'film' => 0,
        'series' => 0,
        'game' => 0
    ];
    $media_count = 0;

    // Construit les fiches et leurs compteurs de catégories.
    while ($media = $query->fetch()) {
        $content .= renderCatalogueCard($media);

        $category = catalogueCategoryKey($media['categories']);
        if ($category !== null) {
            $categories_count[$category]++;
        }
        $media_count++;
    }


    // Prévoit un message adapté lorsque la sélection est vide.
    if ($content === '' || $media_count === 0) {
        $content = $userId === null
            ? "<p class='empty-text'>Aucun média dans le catalogue.</p>"
            : "<p class='empty-text'>Votre collection est vide.</p>";
    }

    echo "<section id='catalogue'>" .
        "<div class='div-filter'>
            <div class='filter-bar'>
                <div class='div-search-bar'>
                    <img class='icon-glass' src='./assets/images/icon_glass.svg' alt='icon de loupe'>
                    <input class='search-bar2' type='text' placeholder='Rechercher une oeuvre...'>
                </div>
                <div class='div-nav-button'>
                    <input id='all' type='radio' name='sort_cards' value='all' class='nav-button' checked>
                        <label for='all'>TOUS</label>
                    <input id='movies' type='radio' name='sort_cards' value='movie' class='nav-button'>
                        <label for='movies'>FILMS ({$categories_count["film"]})</label>
                    <input id='series' type='radio' name='sort_cards' value='serie' class='nav-button'>
                        <label for='series'>SÉRIES ({$categories_count["series"]})</label>
                    <input id='games' type='radio' name='sort_cards' value='game' class='nav-button'>
                        <label for='games'>JEUX ({$categories_count["game"]})</label>
                </div>
                <div class='div-dynamic-filter'>
                    <div class='info-trie'>
                        TRIER PAR
                    </div>
                    <div class='dynamic-filter'>
                        <p class='type-trie'> Plus Récents </p>
                        <img class='drop-down-arrow' src='./assets/images/drop_down_arrow_icon.svg' alt='icon'>
                    </div>
                </div>
            </div>
            <div class='div-nb-media'>
                <div class='funnel-icon'>
                    <img class='drop-down-arrow' src='./assets/images/funnel_icon.svg' alt='icon'>
                </div>
                <div class='nb-media'>
                    $media_count oeuvres affichées sur $media_count
                </div>
            </div>


        </div>" .
        "<div class='content'>" . $content . "</div>" .
        "</section>";
}
