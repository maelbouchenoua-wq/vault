<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
header('Cache-Control: no-store');
$profileUser = currentUser();
$profileName = htmlspecialchars($profileUser['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$profileEmail = htmlspecialchars($profileUser['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
    <!-- Fonts  -->
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
                            <p class="xp-points"> 135 </p>
                            <p class="xp-label"> XP POINTS </p>
                        </div>
                    </div>
                    <div>
                        <div class="current-tier">
                            PALIER ACTUEL
                        </div>
                        <div class="current-level">
                            NIVEAU 2
                        </div>
                    </div>
                </div>
                <div class="progression-info">
                    <div class="title-value-div">
                        <div class="progress-title">
                            Progression vers le niveau 3
                        </div>
                        <div class="progress-value">
                            35 / 100 XP (35%)
                        </div>

                    </div>
                </div>
                <div class="progress-bar-xp">
                </div>
                <div class="level-current-next-div">
                    <div class="level-current">
                        Niv. 2 (100 XP cumulés)
                    </div>
                    <div class="level-next">
                        Prochain palier : 200 XP
                    </div>
                </div>
                <!-- creation of the experience point scale -->
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
    
</main>
<?php include __DIR__ . '/includes/templates/layout/footer.php'; ?>
</body>
</html>