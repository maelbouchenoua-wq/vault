<?php include_once "./includes/categories_manager.php" ?>

<!--TODO: Ajouter la classe 'records_row_active' pour les lignes entrain d'être édité.-->
<tr class="records_row">
    <th scope="row" class="records_id"><?= $media["id"] ?></th>
    <td><img class="records_poster" src="./assets/images/content/<?= $media["image_path"] ?? 'placeholder.svg' ?>" alt="Affiche de <?= $media["title"] ?>"></td>
    <td>
        <div class="records_name"><strong><?= $media["title"] ?></strong></div>
        <p class="records_description"><?= $media["description"] ?></p>
    </td>
    <td><span class="records_category records_category_<?= $media["category"] ?>"><?= get_display_category($media["category"]) ?></span></td>
    <td class="records_year"><?= $media["release_year"] ?></td>
    <td class="records_collections"><strong><?= $media["collection_count"] ?></strong><span>utilisateurs</span></td>
    <td class="records_action">
        <a class="records_edit" href="#record_form" aria-label="Modifier <?= $media["title"] ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="m16 3 5 5M4 15 16 3l5 5L9 20l-6 1z"></path>
            </svg>
        </a>
    </td>
</tr>