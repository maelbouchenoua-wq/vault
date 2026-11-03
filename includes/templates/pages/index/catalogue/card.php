<?php

/** Return the display value of a status */
function getStatusText($status)
{
    return match ($status) {
        'planned' => 'À découvrir',
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
        default => null
    };
}

/**
 * Render the status tag of a card.
 * @param string $tag
 * @return string
 */
function renderCardStatusTag(string $tag = 'planned')
{
    $class = match ($tag) {
        'planned' => 'discover',
        'in_progress' => 'ongoing',
        'completed' => 'finished'
    };

    $text = strtoupper(getStatusText($tag));


    return "
    <div class='element_status $class'>
            <div class='dot'></div>
            <p>$text</p>
        </div>
        ";
}

/** Render a sequence of stars images with the accurate amount of filled stars. */
function renderStarsNotation($starCount)
{
    $content = '';
    for ($i = 1; $i <= $starCount; $i++) {
        $content .= "<img src='/assets/images/star_filled.svg' alt='Étoile de notation $i/5'>";
    }
    for (; $i <= 5; $i++) {
        $content .= "<img src='/assets/images/star_empty.svg' alt='Étoile de notation $i/5'>";
    }

    return $content;
}

/** Generate an HTML card with the given values */

function renderCatalogueCard($media)
{
    $tag = renderCardStatusTag($media["status"]);
    $stars = renderStarsNotation($media["rating"]);
    $status = getStatusText($media["status"]);
    return "<div class='card'>
            <div class='header'>
                    <p class='tag movie'>{$media["categories"]}</p>
                    {$tag}
            </div>
            <div class='overlay_container'>
                <img class='cover' src='./assets/images/content/{$media["image_path"]}' alt='Image de couverture de'>
            </div>
            <div class='footer'>
            <!--TODO: Missing the duration in database. Placeholder value here-->
            <p class='element_infos'>{$media["release_year"]} • 2h 44m</p>
            <div class='information_container'>
                <h2>{$media["title"]}</h2>
                <p class='description'>{$media["description"]}</p>
                </div>
                    <div class='status_container'>
                    <div class='note'>
                        <p>EVALUATION :</p>
                        <div class='star_container'>
                            $stars
                        </div>
                    </div>
                    <div class='status'>
                        <p class='state'>Statut: $status</p>
                        <div class='state_action'>
                        <!--TODO: Missing action button, I cannot infer behavior from figma ¯\_(ツ)_/¯-->
                        </div>
                    </div>
                </div>
            </div>
</div>
";
}