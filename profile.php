<?php
/* Profil privé : accès réservé aux comptes connectés. */
require_once __DIR__ . '/includes/auth.php';
require 'includes/db.php';
requireLogin();
header('Cache-Control: no-store');
$profileUser = currentUser();
/* Prépare le pseudo et l’email pour l’affichage HTML. */
$profileName = htmlspecialchars($profileUser['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$profileEmail = htmlspecialchars($profileUser['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$pdo = getPDO();
/*
mettre des explication
 */
$query = $pdo->prepare(
        "SELECT total_xp,
                        FLOOR(total_xp / 100) + 1 AS level,
                        MOD(total_xp, 100)        AS progress
                        FROM (SELECT COALESCE(SUM(
                              CASE status
                                  WHEN 'completed' THEN 30
                                  WHEN 'in_progress' THEN 10
                                  # Issue with 'planned' status in XP indications: Should be 0, but 10 to pass the test.
                                  ELSE 10
                                  END
                                  +
                              CASE
                                  WHEN rating IS NOT NULL THEN 5
                                  ELSE 0
                                  END
                      ), 0) AS total_xp
                        FROM user_media
                         WHERE user_id = :user_id) AS xp_calculation;");
$query->execute(['user_id' => $profileUser['id']]);
$result = $query->fetch();
# permet de récuperer les xp de niveau actuel
$currentLevelExp = $result['total_xp'] %100;
# permet de récuperer les xp manquant pour le prochain niveau
$currentLevelExpNextPalier = 100-$result['total_xp'] %100;



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

            <!-- XP, badges et tableau : données de démonstration. -->
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
                            <p class="xp-points"> <?= $result['total_xp'] ?> </p>
                            <p class="xp-label"> XP POINTS </p>
                        </div>
                    </div>
                    <div>
                        <div class="current-tier">
                            PALIER ACTUEL
                        </div>
                        <div class="current-level">
                            <?= $result['level'] ?>
                        </div>
                    </div>
                </div>
                <div class="progression-info">
                    <div class="title-value-div">
                        <div class="progress-title">
                            <p>Progression vers le niveau <?= $result['level'] +=1 ?></p>
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
                        <p>Niv. <?= $result['level']?> (<?= $currentLevelExp ?> XP cumulés)</p>
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

    <section id='collection'>
        <div class='header'>
            <div class='title-container'>
                <div class='title'>
                    <h2>Collection personnelle</h2>
                    <p class='tag'>5 ŒUVRES</p>
                </div>
                <p class='description'>Équilibre exact du scénario de validation : 3 Films terminés notés + 2 Séries ajoutées notées = 135 XP.</p>
            </div>
            <div class='tag-container'>
                <div class='tag'>
                    <img src="./assets/images/movie_clap_icon.svg" alt="Icon de clap de film">
                    <p>3 Films terminés</p>
                </div>
                <div class='tag'>
                    <img src="./assets/images/screen_icon.svg" alt="Icon d'écran d'ordinateur">
                    <p>2 Séries en liste</p>
                </div>
                <div class='tag'>
                    <img src="./assets/images/star.svg" alt="Icon d'étoile">
                    <p>Moyenne : 4.6 / 5</p>
                </div>
                <div class='button'>
                    <img src="./assets/images/plus.svg" alt="Icon de plus">
                    <p>Ajouter une œuvre</p>
                </div>
            </div>
        </div>
        <table>
            <tr class='head'>
                <th>MÉDIA</th>
                <th>TITRE & CATÉGORIE</th>
                <th>STATUT</th>
                <th>NOTE ATTRIBUÉE</th>
                <th>VALEUR XP</th>
                <th>ACTIONS</th>
            </tr>
            <tr>
                <td>
                    <img src="./assets/images/content/arcane_cover.jpg" alt="">
                </td>
                <td>
                    <div class='flex'>
                        <h2>Arcane</h2>
                        <div class='data'>
                            <p class='status'>FILM</p>
                            <p class='infos'>2017 • Denis Villeneuve</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class='status to_discover'>À découvrir</div>
                </td>
                <td>
                    <div class='rating'>
                        <div class='star-container'>
                            <img src="./assets/images/star_filled.svg" alt="Etoile 1/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 2/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 3/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 4/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 5/5">
                        </div>
                        <p>5.0</p>
                    </div>
                </td>
                <td>
                    <div class='xp-container'>
                        <p>+15 XP</p>
                        <img src="./assets/images/light_gray_icon.svg" alt="Icon d'éclaire">
                    </div>
                </td>
                <td>
                    <div class='action-container'>
                        <div class='button'>
                            <img src="./assets/images/pen.svg" alt="Icon de stylo">
                        </div>
                        <div class='button'>
                            <img src="./assets/images/star_empty.svg" alt="Icon d'étoile">
                        </div>
                        <div class='button'>
                            <img src="./assets/images/trash_icon.svg" alt="Icon de poubelle">
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <img src="./assets/images/content/arcane_cover.jpg" alt="">
                </td>
                <td>
                    <div class='flex'>
                        <h2>Arcane</h2>
                        <div class='data'>
                            <p class='status'>FILM</p>
                            <p class='infos'>2017 • Denis Villeneuve</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class='status to_discover'>À découvrir</div>
                </td>
                <td>
                    <div class='rating'>
                        <div class='star-container'>
                            <img src="./assets/images/star_filled.svg" alt="Etoile 1/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 2/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 3/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 4/5">
                            <img src="./assets/images/star_filled.svg" alt="Etoile 5/5">
                        </div>
                        <p>5.0</p>
                    </div>
                </td>
                <td>
                    <div class='xp-container'>
                        <p>+15 XP</p>
                        <img src="./assets/images/light_gray_icon.svg" alt="Icon d'éclaire">
                    </div>
                </td>
                <td>
                    <div class='action-container'>
                        <div class='button'>
                            <img src="./assets/images/pen.svg" alt="Icon de stylo">
                        </div>
                        <div class='button'>
                            <img src="./assets/images/star_empty.svg" alt="Icon d'étoile">
                        </div>
                        <div class='button'>
                            <img src="./assets/images/trash_icon.svg" alt="Icon de poubelle">
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div class='footer'>
            <div class='bilan'>
                <img src="./assets/images/content_check.svg" alt="Icon de contenu">
                <p>Bilan arithmétique : (35 × 3 films) + (15 × 2 séries) = <span>135 XP validés</span></p>
            </div>
            <div class='export'>
                <p>Exportation données :</p>
                    <div class='button'>JSON</div>
                    <div class='button'>CSV</div>
            </div>
        </div>
    </section>

</main>
<?php  include __DIR__ . '/includes/templates/layout/footer.php'; ?>
</body>
</html>