<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/includes/db.php';
require_once "card.php";

/**
 * Generate the catalogue section on the index page.
 * Will fetch all the data from the database.
 */
function getCatalogue(int $userId)
    //TODO: Replace `$userId` with a proper user abstraction when implementing auth systems.
{
    # Fetch data
    $pdo = getPDO();
    $query = $pdo->query(
        "SELECT users.id as utilisateur, user_media.status, user_media.rating, medias.title, medias.image_path, medias.release_year, medias.description, categories.name as categories
                FROM user_media
                         INNER JOIN medias ON medias.id = user_media.media_id
                         INNER JOIN users ON users.id = user_media.user_id
                         INNER JOIN categories ON categories.id = medias.category_id
                WHERE user_id = $userId;"
    );


    # Render the list
    $content = '';
    while ($media = $query->fetch()) {
        $content .= renderCatalogueCard($media);
    }

    if ($content === '') {
        $content = "<p class='empty-text'>Vous n'avez aucun média</p>";
    }
    //TODO: Filter and sort should be dynamic
    echo '<section id="catalogue">' .
        '<div class="div-filter">
            <div class="filter-bar">
                <div class="div-search-bar">
                    <img class="icon-glass" src="./assets/images/icon_glass.svg" alt="icon de loupe">
                    <input class="search-bar2" type="text" placeholder="Rechercher une oeuvre...">
                </div>
                <div class="div-nav-button">

                    <div class="nav-button1">
                        TOUS
                    </div>
                    <div class="nav-button2">
                        FILMS (3)
                    </div>
                    <div class="nav-button2">
                        SÉRIES (3)
                    </div>
                    <div class="nav-button2">
                        JEUX (3)
                    </div>
                </div>
                <div class="div-dynamic-filter">
                    <div class="info-trie">
                        TRIER PAR
                    </div>
                    <div class="dynamic-filter">
                        <p class="type-trie"> Plus Récents </p>
                        <img class="drop-down-arrow" src="./assets/images/drop_down_arrow_icon.svg" alt="icon">

                    </div>
                </div>
            </div>
            <div class="div-nb-media">
                <div class="funnel-icon">
                    <img class="drop-down-arrow" src="./assets/images/funnel_icon.svg" alt="icon">
                </div>
                <div class="nb-media">
                    9 oeuvres affichées sur 9
                </div>
            </div>


        </div>' .
        '<div class="content">' . $content . '</div>' .
        '</section>';
}