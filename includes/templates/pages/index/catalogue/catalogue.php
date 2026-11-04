<?php
require_once dirname(__DIR__, 4) . '/db.php';
require_once __DIR__ . '/card.php';

/**
 * Generate the catalogue section on the index page.
 * Will fetch all the data from the database.
 */
function getCatalogue(?int $userId = null)
{
    # Fetch data
    $pdo = getPDO();
    $query = $pdo->prepare(
        "SELECT medias.id,
                user_media.user_id AS utilisateur,
                COALESCE(user_media.status, 'planned') AS status,
                user_media.rating,
                medias.title,
                medias.image_path,
                medias.release_year,
                medias.description,
                categories.name AS categories
         FROM medias
         INNER JOIN categories ON categories.id = medias.category_id
         LEFT JOIN user_media ON user_media.media_id = medias.id
                             AND user_media.user_id = :userId
         WHERE (:isVisitor = 1 OR user_media.user_id IS NOT NULL)
         ORDER BY medias.id DESC"
    );
    $query->bindValue(':userId', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $query->bindValue(':isVisitor', $userId === null ? 1 : 0, PDO::PARAM_INT);
    $query->execute();

    # Render the list and aggregates counters for display
    $content = '';
    $categories_count = [
        'film' => 0,
        'series' => 0,
        'game' => 0
    ];
    $media_count = 0;
    while ($media = $query->fetch()) {
        $content .= renderCatalogueCard($media);
        $category = catalogueCategoryKey($media['categories']);
        if ($category !== null) {
            $categories_count[$category]++;
        }
        $media_count++;
    }


    if ($content === '' || $media_count === 0) {
        $content = $userId === null
            ? "<p class='empty-text'>Aucun média dans le catalogue.</p>"
            : "<p class='empty-text'>Votre collection est vide.</p>";
    }
    //TODO: Filter and sort should be dynamic
    echo "<section id='catalogue'>" .
        "<div class='div-filter'>
            <div class='filter-bar'>
                <div class='div-search-bar'>
                    <img class='icon-glass' src='./assets/images/icon_glass.svg' alt='icon de loupe'>
                    <input class='search-bar2' type='text' placeholder='Rechercher une oeuvre...'>
                </div>
                <div class='div-nav-button'>
                    <input id='all' type='radio' name='sort' value='all' class='nav-button' checked>
                        <label for='all'>TOUS</label>
                    <input id='movies' type='radio' name='sort' value='movies' class='nav-button'>
                        <label for='movies'>FILMS ({$categories_count["film"]})</label>
                    <input id='series' type='radio' name='sort' value='series' class='nav-button'>
                        <label for='series'>SÉRIES ({$categories_count["series"]})</label>
                    <input id='games' type='radio' name='sort' value='games' class='nav-button'>
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
