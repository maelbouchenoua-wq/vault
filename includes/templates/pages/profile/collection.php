<?php
// Le contrôleur fournit les données et les erreurs déjà validées côté serveur.
$collectionIsAdd = $collectionMode === 'add';
$collectionIsRemove = $collectionMode === 'remove';
$collectionShowEditor = in_array($collectionMode, ['add', 'edit', 'remove'], true);
$collectionStatusValue = 'planned';
if (isset($collectionEntry['status'])) {
    $collectionStatusValue = (string) $collectionEntry['status'];
}
if (isset($collectionForm['status'])) {
    $collectionStatusValue = (string) $collectionForm['status'];
}
$collectionRatingValue = '';
if (isset($collectionForm['rating'])) {
    $collectionRatingValue = (string) $collectionForm['rating'];
}
$collectionMediaValue = '';
if (isset($collectionForm['media_id'])) {
    $collectionMediaValue = (string) $collectionForm['media_id'];
}
$collectionAverageLabel = 'Non noté';
if ($collectionStats['average'] !== null) {
    $collectionAverageLabel = number_format((float) $collectionStats['average'], 1, '.', '') . ' / 5';
}

// Les libellés dépendent du formulaire demandé.
$collectionEditorTitle = 'Modifier une œuvre';
$collectionAction = 'update';
$collectionSubmitLabel = 'Enregistrer';
$collectionSubmitClass = 'collection-submit';
if ($collectionIsAdd) {
    $collectionEditorTitle = 'Ajouter une œuvre';
    $collectionAction = 'add';
    $collectionSubmitLabel = 'Ajouter';
} elseif ($collectionIsRemove) {
    $collectionEditorTitle = 'Retirer une œuvre';
    $collectionAction = 'remove';
    $collectionSubmitLabel = 'Retirer de ma collection';
    $collectionSubmitClass .= ' collection-submit-danger';
}
$collectionNoChoices = $collectionChoices === [];
$collectionAddDisabled = $collectionIsAdd && $collectionNoChoices;
$collectionFormDescription = 'collection-editor-description';
if ($collectionError !== null) {
    $collectionFormDescription .= ' collection-error';
}
?>
<section id="collection" class="collection-section" aria-labelledby="collection-title">
    <div class="header collection-header">
        <div class="title-container">
            <div class="title">
                <h2 id="collection-title">Collection personnelle</h2>
                <p class="tag"><span data-collection-stat="count"><?= collectionHtml($collectionStats['count']) ?></span> ŒUVRES</p>
            </div>
            <p class="description">Vos œuvres, vos statuts et vos notes réunis au même endroit.</p>
        </div>
        <div class="tag-container">
            <div class="tag">
                <img src="<?= collectionHtml(authUrl('assets/images/movie_clap_icon.svg')) ?>" alt="">
                <p><span data-collection-stat="completed-films"><?= collectionHtml($collectionStats['completed_films']) ?></span> films terminés</p>
            </div>
            <div class="tag">
                <img src="<?= collectionHtml(authUrl('assets/images/screen_icon.svg')) ?>" alt="">
                <p><span data-collection-stat="series"><?= collectionHtml($collectionStats['series_count']) ?></span> séries en liste</p>
            </div>
            <div class="tag">
                <img src="<?= collectionHtml(authUrl('assets/images/star.svg')) ?>" alt="">
                <p>Moyenne : <span data-collection-stat="average"><?= collectionHtml($collectionAverageLabel) ?></span></p>
            </div>
            <a class="button collection-add-link" href="<?= collectionHtml(authUrl('profile.php?collection=add') . '#collection-editor') ?>">
                <img src="<?= collectionHtml(authUrl('assets/images/plus.svg')) ?>" alt="">
                Ajouter une œuvre
            </a>
        </div>
    </div>

    <?php if ($collectionFlash !== null):
        $collectionFlashClass = 'collection-flash-error';
        $collectionFlashRole = 'alert';
        if ($collectionFlash['type'] === 'success') {
            $collectionFlashClass = 'collection-flash-success';
            $collectionFlashRole = 'status';
        }
    ?>
        <p class="collection-flash <?= collectionHtml($collectionFlashClass) ?>" role="<?= collectionHtml($collectionFlashRole) ?>"><?= collectionHtml($collectionFlash['message']) ?></p>
    <?php endif; ?>
    <?php if ($collectionError !== null): ?>
        <p id="collection-error" class="collection-flash collection-flash-error" role="alert"><?= collectionHtml($collectionError) ?></p>
    <?php endif; ?>

    <?php if ($collectionShowEditor): ?>
        <!-- Les liens ouvrent l'éditeur ; seule la validation POST modifie la collection. -->
        <section id="collection-editor" class="collection-editor" aria-labelledby="collection-editor-title">
            <div class="collection-editor-heading">
                <h3 id="collection-editor-title"><?= collectionHtml($collectionEditorTitle) ?></h3>
                <a class="collection-cancel" href="<?= collectionHtml(authUrl('profile.php') . '#collection') ?>">Annuler</a>
            </div>
            <?php if (!$collectionIsAdd && $collectionEntry === null): ?>
                <p>Cette entrée est introuvable dans votre collection.</p>
            <?php else: ?>
                <p id="collection-editor-description" class="collection-editor-description">
                    <?php if ($collectionIsRemove): ?>
                        Retirer « <strong><?= collectionHtml($collectionEntry['title']) ?></strong> » de votre collection ? L’œuvre restera disponible dans le catalogue.
                    <?php elseif ($collectionIsAdd): ?>
                        Choisissez une œuvre du catalogue, puis son statut et une note facultative.
                    <?php else: ?>
                        Mettez à jour le statut et la note de « <strong><?= collectionHtml($collectionEntry['title']) ?></strong> ».
                    <?php endif; ?>
                </p>
                <?php if ($collectionAddDisabled): ?>
                    <p id="collection-no-choice" class="collection-no-choice">Aucune œuvre disponible à ajouter : votre collection contient déjà toutes les œuvres du catalogue.</p>
                <?php endif; ?>
                <form id="collection-form" class="collection-form" action="<?= collectionHtml(authUrl('includes/actions/collection_action.php')) ?>" method="post" aria-describedby="<?= collectionHtml($collectionFormDescription) ?>">
                    <input type="hidden" name="csrf_token" value="<?= collectionHtml($collectionToken) ?>">
                    <input type="hidden" name="action" value="<?= collectionHtml($collectionAction) ?>">
                    <?php if (!$collectionIsAdd): ?>
                        <input type="hidden" name="entry_id" value="<?= collectionHtml($collectionEntry['id']) ?>">
                    <?php endif; ?>

                    <?php if (!$collectionIsRemove): ?>
                        <div class="collection-fields">
                            <?php if ($collectionIsAdd): ?>
                                <div class="collection-field collection-media-field">
                                    <label for="collection-media">Œuvre du catalogue</label>
                                    <select id="collection-media" name="media_id" required<?php if ($collectionNoChoices): ?> disabled aria-describedby="collection-no-choice"<?php endif; ?>>
                                        <option value="">Choisir une œuvre</option>
                                        <?php foreach ($collectionChoices as $collectionChoice):
                                            $collectionChoiceLabel = $collectionChoice['title'] . ' — ' . $collectionChoice['category_label'];
                                            if ($collectionChoice['release_year'] !== null && $collectionChoice['release_year'] !== '') {
                                                $collectionChoiceLabel .= ' · ' . $collectionChoice['release_year'];
                                            }
                                            $collectionChoiceSelected = $collectionMediaValue === (string) $collectionChoice['media_id'];
                                        ?>
                                            <option value="<?= collectionHtml($collectionChoice['media_id']) ?>"<?php if ($collectionChoiceSelected): ?> selected<?php endif; ?>><?= collectionHtml($collectionChoiceLabel) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="collection-field">
                                <label for="collection-status">Statut</label>
                                <select id="collection-status" name="status" required>
                                    <option value="planned"<?php if ($collectionStatusValue === 'planned'): ?> selected<?php endif; ?>>À découvrir</option>
                                    <option value="in_progress"<?php if ($collectionStatusValue === 'in_progress'): ?> selected<?php endif; ?>>En cours</option>
                                    <option value="completed"<?php if ($collectionStatusValue === 'completed'): ?> selected<?php endif; ?>>Terminé</option>
                                </select>
                            </div>
                            <div class="collection-field">
                                <label for="collection-rating">Note (facultative)</label>
                                <select id="collection-rating" name="rating">
                                    <option value=""<?php if ($collectionRatingValue === ''): ?> selected<?php endif; ?>>Non noté</option>
                                    <?php for ($collectionScore = 1; $collectionScore <= 5; $collectionScore++): ?>
                                        <option value="<?= collectionHtml($collectionScore) ?>"<?php if ($collectionRatingValue === (string) $collectionScore): ?> selected<?php endif; ?>><?= collectionHtml($collectionScore) ?> / 5</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="collection-form-actions">
                        <button type="submit" class="<?= collectionHtml($collectionSubmitClass) ?>"<?php if ($collectionAddDisabled): ?> disabled<?php endif; ?>><?= collectionHtml($collectionSubmitLabel) ?></button>
                        <a class="collection-cancel" href="<?= collectionHtml(authUrl('profile.php') . '#collection') ?>">Annuler</a>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <div class="collection-table-scroll" role="region" tabindex="0" aria-labelledby="collection-title">
        <table class="collection-table">
            <caption class="collection-sr-only">Œuvres de votre collection, avec leur statut, leur note et leur valeur en expérience.</caption>
            <thead>
                <tr class="head">
                    <th scope="col">Média</th>
                    <th scope="col">Titre &amp; catégorie</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Note attribuée</th>
                    <th scope="col">Valeur XP</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($collectionItems === []): ?>
                    <tr><td colspan="6" class="collection-empty">Votre collection est vide. Ajoutez une première œuvre pour commencer.</td></tr>
                <?php endif; ?>
                <?php foreach ($collectionItems as $collectionItem): ?>
                    <?php
                    $collectionItemRating = null;
                    $collectionRatingLabel = 'Non noté';
                    $collectionRatingClass = 'collection-unrated';
                    $collectionRatingText = 'Non noté';
                    if ($collectionItem['rating'] !== null) {
                        $collectionItemRating = (int) $collectionItem['rating'];
                        $collectionRatingLabel = 'Note : ' . $collectionItemRating . ' sur 5';
                        $collectionRatingClass = '';
                        $collectionRatingText = $collectionItemRating . '.0';
                    }
                    $collectionYearLabel = 'Année non renseignée';
                    if ($collectionItem['release_year'] !== null && $collectionItem['release_year'] !== '') {
                        $collectionYearLabel = $collectionItem['release_year'];
                    }
                    $collectionEditUrl = authUrl('profile.php?collection=edit&entry_id=' . (int) $collectionItem['id']) . '#collection-editor';
                    $collectionRemoveUrl = authUrl('profile.php?collection=remove&entry_id=' . (int) $collectionItem['id']) . '#collection-editor';
                    ?>
                    <tr data-collection-entry="<?= collectionHtml($collectionItem['id']) ?>" data-media-id="<?= collectionHtml($collectionItem['media_id']) ?>">
                        <td>
                            <?php if ($collectionItem['image_url'] !== ''): ?>
                                <img src="<?= collectionHtml($collectionItem['image_url']) ?>" width="56" height="80" alt="" loading="lazy">
                            <?php else: ?>
                                <span class="collection-cover-placeholder" aria-label="Aucun visuel">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex">
                                <h3 class="collection-item-title"><?= collectionHtml($collectionItem['title']) ?></h3>
                                <div class="data">
                                    <p class="status"><?= collectionHtml($collectionItem['category_label']) ?></p>
                                    <p class="infos"><?= collectionHtml($collectionYearLabel) ?></p>
                                </div>
                            </div>
                        </td>
                        <td><div class="status <?= collectionHtml($collectionItem['status_class']) ?>" data-collection-status="<?= collectionHtml($collectionItem['status']) ?>"><?= collectionHtml($collectionItem['status_label']) ?></div></td>
                        <td>
                            <div class="rating" aria-label="<?= collectionHtml($collectionRatingLabel) ?>">
                                <div class="star-container" aria-hidden="true">
                                    <?php for ($collectionStar = 1; $collectionStar <= 5; $collectionStar++):
                                        $collectionStarIcon = 'star_empty.svg';
                                        if ($collectionItemRating !== null && $collectionStar <= $collectionItemRating) {
                                            $collectionStarIcon = 'star_filled.svg';
                                        }
                                        $collectionStarUrl = authUrl('assets/images/' . $collectionStarIcon);
                                    ?>
                                        <img src="<?= collectionHtml($collectionStarUrl) ?>" alt="" width="15" height="15">
                                    <?php endfor; ?>
                                </div>
                                <p class="<?= collectionHtml($collectionRatingClass) ?>"><?= collectionHtml($collectionRatingText) ?></p>
                            </div>
                        </td>
                        <td><div class="xp-container"><p>+<?= collectionHtml($collectionItem['xp']) ?> XP</p><img src="<?= collectionHtml(authUrl('assets/images/light_gray_icon.svg')) ?>" alt=""></div></td>
                        <td>
                            <div class="action-container">
                                <a class="button collection-action" href="<?= collectionHtml($collectionEditUrl) ?>" aria-label="Modifier <?= collectionHtml($collectionItem['title']) ?>" title="Modifier <?= collectionHtml($collectionItem['title']) ?>"><img src="<?= collectionHtml(authUrl('assets/images/pen.svg')) ?>" alt=""></a>
                                <a class="button collection-action" href="<?= collectionHtml($collectionEditUrl) ?>" aria-label="Noter <?= collectionHtml($collectionItem['title']) ?>" title="Noter <?= collectionHtml($collectionItem['title']) ?>"><img src="<?= collectionHtml(authUrl('assets/images/star_empty.svg')) ?>" alt=""></a>
                                <a class="button collection-action collection-action-remove" href="<?= collectionHtml($collectionRemoveUrl) ?>" aria-label="Supprimer <?= collectionHtml($collectionItem['title']) ?>" title="Supprimer <?= collectionHtml($collectionItem['title']) ?>"><img src="<?= collectionHtml(authUrl('assets/images/trash_icon.svg')) ?>" alt=""></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Le bilan utilise le même barème que le calcul serveur des XP. -->
    <div class="footer collection-footer">
        <div class="bilan">
            <img src="<?= collectionHtml(authUrl('assets/images/content_check.svg')) ?>" alt="">
            <div>
                <p>Bilan : (10 × <?= collectionHtml($collectionStats['count']) ?>) + (20 × <?= collectionHtml($collectionStats['completed_count']) ?>) + (5 × <?= collectionHtml($collectionStats['rated_count']) ?>) = <span><strong data-collection-stat="total-xp"><?= collectionHtml($collectionStats['total_xp']) ?></strong> XP validés</span></p>
                <p class="collection-xp-rule">Collection : +10 XP · Statut terminé : +20 XP · Note valide : +5 XP</p>
            </div>
        </div>
        <div class="export">
            <p>Exporter :</p>
            <a class="button" href="<?= collectionHtml(authUrl('includes/actions/export_collection.php?format=json')) ?>" aria-label="Exporter ma collection au format JSON">JSON</a>
            <a class="button" href="<?= collectionHtml(authUrl('includes/actions/export_collection.php?format=csv')) ?>" aria-label="Exporter ma collection au format CSV">CSV</a>
        </div>
    </div>
</section>