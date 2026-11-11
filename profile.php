<?php
require_once __DIR__ . '/includes/libs/auth.php';
require_once __DIR__ . '/includes/controllers/collection_controller.php';
requireLogin();

$profileUser = currentUser();
$profileName = collectionHtml($profileUser['username']);
$profileEmail = collectionHtml($profileUser['email']);
$collectionToken = csrfToken();

// Récupère le message laissé après l'envoi du formulaire.
$collectionFlash = null;
if (isset($_SESSION['collection_flash']) && is_array($_SESSION['collection_flash'])) {
    $collectionFlash = $_SESSION['collection_flash'];
}
unset($_SESSION['collection_flash']);

// Le lien choisi indique quel formulaire afficher.
$collectionMode = null;
$modesPossibles = ['add', 'edit', 'remove'];
if (isset($_GET['collection']) && is_string($_GET['collection'])) {
    if (in_array($_GET['collection'], $modesPossibles, true)) {
        $collectionMode = $_GET['collection'];
    }
}

$collectionEntry = null;
$collectionError = null;
$collectionChoices = [];
$collectionItems = [];
$collectionStats = [
    'count' => 0,
    'completed_films' => 0,
    'series_count' => 0,
    'rated_count' => 0,
    'average' => null,
    'total_xp' => 0,
    'completed_count' => 0,
    'level' => 1,
    'progress' => 0,
    'remaining' => 100
];
$collectionForm = ['media_id' => '', 'status' => 'planned', 'rating' => ''];

try {
    $collection = getProfileCollection($profileUser['id']);
    $collectionItems = $collection['items'];
    $collectionStats = $collection['stats'];

    if ($collectionMode === 'add') {
        $collectionChoices = getCollectionChoices($profileUser['id']);
    }

    if ($collectionMode === 'edit' || $collectionMode === 'remove') {
        $entryId = false;
        if (isset($_GET['entry_id']) && is_string($_GET['entry_id'])) {
            $entryId = filter_var($_GET['entry_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        }

        // Cherche l'œuvre uniquement dans la collection de ce compte.
        foreach ($collectionItems as $item) {
            if ($item['id'] === $entryId) {
                $collectionEntry = $item;
                $collectionForm['media_id'] = (string) $item['media_id'];
                $collectionForm['status'] = $item['status'];
                if ($item['rating'] !== null) {
                    $collectionForm['rating'] = (string) $item['rating'];
                }
                break;
            }
        }

        if ($collectionEntry === null) {
            $collectionMode = null;
            $collectionError = 'Cette œuvre ne fait pas partie de votre collection.';
        }
    }

    // Après une erreur, remet les valeurs saisies dans le formulaire.
    if ($collectionMode !== null && isset($collectionFlash['form']) && is_array($collectionFlash['form'])) {
        foreach ($collectionForm as $field => $value) {
            if (isset($collectionFlash['form'][$field]) && is_string($collectionFlash['form'][$field])) {
                $collectionForm[$field] = $collectionFlash['form'][$field];
            }
        }
    }
} catch (PDOException $exception) {
    http_response_code(503);
    $collectionMode = null;
    $collectionError = 'Votre collection est momentanément indisponible. Veuillez réessayer plus tard.';
}

// Réutilise les mêmes XP dans la carte du profil.
$result = [
    'total_xp' => $collectionStats['total_xp'],
    'level' => $collectionStats['level']
];
$currentLevelExp = $collectionStats['progress'];
$currentLevelExpNextPalier = $collectionStats['remaining'];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Vault - Mon profil</title>
    <link rel="stylesheet" href="./assets/css/reset.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/components/header.css">
    <link rel="stylesheet" href="./assets/css/components/footer.css">
    <link rel="stylesheet" href="./assets/css/pages/profile.css">
    <link rel="stylesheet" href="./assets/css/pages/profile-collection.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Space+Grotesk:wght@300..700&display=swap"
          rel="stylesheet">
</head>
<body>
<?php
$current_page = "profil";
include __DIR__ . '/includes/templates/layout/header.php';
?>
<main>
    <div class="profile">
        <div class="profile2">

            <img class="profile_picture2" src="./assets/images/profil-picture2.jpg" alt="Photo de profil">
            <div>

                <div class="title-ref-div">
                    <p class="title"> ARCHIVISTE AGREE</p>
                    <p class="ref"> // REF #VX-<?= (int) $profileUser['id'] ?></p>
                </div>
                <div class="name">
                    <?= $profileName ?>
                </div>

                <div class="id-infoDate-div">
                    <div class="id">
                        @<?= $profileName ?>
                    </div>
                    <div class="info-date">
                        <?= $profileEmail ?>
                    </div>
                </div>

                <div class="rang-statut-div">
                    <div class="rang">
                        <img src="./assets/images/rang-icon.svg" alt="icone">
                        <p> Rang : II Synth-Pionnier </p>
                    </div>
                    <div class="statut">
                        <img src="assets/images/certification-icon.svg" alt="icone">
                        <p><?= $profileUser['role'] === 'admin' ? 'Compte administrateur' : 'Compte utilisateur' ?></p>
                    </div>
                </div>
            </div>

            <!-- Résumé de l’expérience calculée depuis la collection. -->
            <div class="info-profile">
                <div class="xp-div">
                    <div class="lightning-frame">
                        <img src="assets/images/lightning-icon.svg" alt="icone">
                    </div>
                    <div>
                        <div class="xp-info">
                            EXPERIENCE TOTALE
                        </div>
                        <div class=xp-points-label-div>
                            <p class="xp-points" data-profile-stat="xp"> <?= $result['total_xp'] ?> </p>
                            <p class="xp-label"> XP POINTS </p>
                        </div>
                    </div>
                    <div>
                        <div class="current-tier">
                            PALIER ACTUEL
                        </div>
                        <div class="current-level" data-profile-stat="level">
                            <?= $result['level'] ?>
                        </div>
                    </div>
                </div>
                <div class="progression-info">
                    <div class="title-value-div">
                        <div class="progress-title">
                            <p>Progression vers le niveau <?= $result['level'] + 1 ?></p>
                        </div>
                        <div class="progress-value">
                            <p><?= $currentLevelExp ?>
                             / 100 XP (<?= $currentLevelExp ?>%)</p>
                        </div>

                    </div>
                </div>
                <div class="progress-bar-xp">
                    <div class="progress-bar-fill"
                         style="width: <?= $currentLevelExp ?>%;">
                    </div>
                </div>
                <div class="level-current-next-div">
                    <div class="level-current">
                        <p>Niv. <?= $result['level']?> (<?= $result['total_xp'] ?> XP cumulés)</p>
                    </div>
                    <div class="level-next">
                        Prochain palier : (<?= $currentLevelExpNextPalier ?>XP)
                    </div>
                </div>
                <div class="scale">
                    <div class="information-scale">
                        <img src="assets/images/info-icon.svg" alt="icone">
                        <p> BAREME DES GAINS D'EXPÉRIENCE (VAULT PROTOCOLE) </p>
                    </div>

                    <div class="new-xp">
                        <div class="new-xp-info">
                            <p class="xp-count">
                                +10 XP
                            </p>
                            <p class="xp-label2">
                                Ajout collection
                            </p>
                        </div>
                        <div class="new-xp-info">
                            <p class="xp-count">
                                +20 XP
                            </p>
                            <p class="xp-label2">
                                Statut "Terminé"
                            </p>
                        </div>
                        <div class="new-xp-info">
                            <p class="xp-count">
                                +5 XP
                            </p>
                            <p class="xp-label2">
                                Note valide (★)
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <section id="badges">
        <div class="title_container">
            <div>
                <p class="subtitle">REGISTRE DE DISTINCTION</p>
                <h2>Badges Archivistiques Débloqués</h2>
            </div>
            <div>
                <p>
                    Complétion trophées : <span>2 sur 4 actifs</span>
                </p>
            </div>
        </div>
        <div class="badges_container">
            <div class="badge purple">
                <div class="content">
                    <div class="header">
                        <div class="icon_container">
                            <img src="assets/images/diamond_purple.svg" alt="Icon de diamant">
                        </div>
                        <div class="status">
                            <img src="assets/images/check_purple.svg" alt="Icon de coche">
                            <p>OBTENU</p>
                        </div>
                    </div>
                    <div class="main">
                        <h3>Collectionneur</h3>
                        <p>Posséder au moins 5 œuvres dans sa collection personnelle.</p>
                    </div>
                </div>
                <div class="footer">
                    <div class="objective">
                        <p>Condition d'acquisition</p>
                        <p class="colored">5 / 5 enregistrées (100%)</p>
                    </div>
                </div>
            </div>
            <div class="badge gold">
                <div class="content">
                    <div class="header">
                        <div class="icon_container">
                            <img src="assets/images/clap_gold.svg" alt="Icon de clap">
                        </div>
                        <div class="status">
                            <img src="assets/images/check_gold.svg" alt="Icon de coche">
                            <p>OBTENU</p>
                        </div>
                    </div>
                    <div class="main">
                        <h3>Cinéphile</h3>
                        <p>Avoir terminé le visionnage d'au moins 3 films du catalogue.</p>
                    </div>
                </div>
                <div class="footer">
                    <div class="objective">
                        <p>Condition d'acquisition</p>
                        <p class="colored">3 / 3 visionnés (100%)</p>
                    </div>
                </div>
            </div>
            <div class="badge locked">
                <div class="content">
                    <div class="header">
                        <div class="icon_container">
                            <img src="assets/images/controller.svg" alt="Icon de manette de jeu">
                        </div>
                        <div class="status">
                            <img src="assets/images/lock.svg" alt="Icon de cadenas">
                            <p>VERROUILLÉ</p>
                        </div>
                    </div>
                    <div class="main">
                        <h3>Gamer</h3>
                        <p>Avoir terminé au moins 3 jeux vidéo répertoriés.</p>
                    </div>
                </div>
                <div class="footer">
                    <div class="progress">
                        <div class="header">
                            <p>Progression</p>
                            <p class="obj">0 / 3 jeux terminés</p>
                        </div>
                        <progress max="100" value="0" class="progress_bar"></progress>
                    </div>
                </div>
            </div>
            <div class="badge locked">
                <div class="content">
                    <div class="header">
                        <div class="icon_container">
                            <img src="assets/images/target.svg" alt="Icon de cible">
                        </div>
                        <div class="status">
                            <img src="assets/images/lock.svg" alt="Icon de cadenas">
                            <p>VERROUILLÉ</p>
                        </div>
                    </div>
                    <div class="main">
                        <h3>Explorateur</h3>
                        <p>Enregistrer un score d'au moins 15 au mini-jeu interactif.</p>
                    </div>
                </div>
                <div class="footer">
                    <div class="progress">
                        <div class="header">
                            <p>Progression</p>
                            <p class="obj">0 / 3 jeux terminés</p>
                        </div>
                        <progress max="100" value="0" class="progress_bar"></progress>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/includes/templates/pages/profile/collection.php'; ?>

</main>
<?php  include __DIR__ . '/includes/templates/layout/footer.php'; ?>
</body>
</html>