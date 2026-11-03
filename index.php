<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Vault - Accueil</title>
    <link rel="stylesheet" href="./assets/css/reset.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/components/header.css">
    <link rel="stylesheet" href="./assets/css/components/footer.css">
    <link rel="stylesheet" href="./assets/css/pages/index.css">
    <!-- Fonts  -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Space+Grotesk:wght@300..700&display=swap"
          rel="stylesheet">
</head>
<body>
<?php
$current_page = "index";
include __DIR__ . '/includes/templates/header.php';
?>
<main>
    <div class="hero-banner">
        <div class="sprint-bdd-info">
            <div class="number-index-div">
                <div class="sprint-number">
                    ARCHIVE_NATIVE // SPRINT_01
                </div>
                <div class="index">
                    INDEX CENTRALISE MULTIMEDIA
                </div>
            </div>

            <div class="bdd-xp-div">
                <div class="bdd-icon">
                    <img src="./assets/images/bdd-icon.svg" alt="icon">
                </div>
                <div class="bdd-info">
                    INDEX : 9 OEUVRE // SQLITE LOCAL
                </div>
                <div class="light-icon">
                    <img src="./assets/images/light_icon.svg" alt="icon">
                </div>
                <div class="xp-active">
                    XP SYNC ACTIVE
                </div>
            </div>
        </div>
        <h1> Explorer votre <span class="h1-color"> univers pop-culture </span></h1>

        <div class="banner-txt">
            <p>Conservez vos films favoris, suivez vos séries et bâtissez votre collection de jeux vidéo <br/>
                au sein d'un sanctuaire numérique haute fidélité.</p>
        </div>

        <div class="div-filter">
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


        </div>


    </div>
    <section id="catalogue">
        <div class="card">
            <div class="header">
                <p class="tag movie">FILM</p>
                <div class="element_status finished">
                    <div class="dot"></div>
                    <p>TERMINÉ</p>
                </div>
            </div>
            <div class="overlay_container">
                <img class="cover" src="./assets/images/content/blade_runner_cover.jpg" alt="Image de couverture de">
            </div>
            <div class="footer">
                <p class="element_infos">
                    2017 • 2h 44m
                </p>
                <div class="information_container">
                    <h2>Blade Runner 2049</h2>
                    <p class="description">Trente ans après les événements du premier film, un nouveau blade runner, l'officier K, déterre un secret…</p>
                </div>
                <div class="status_container">
                    <div class="note">
                        <p>EVALUATION :</p>
                        <div class="star_container">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 1/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 2/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 3/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 4/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 5/5">
                        </div>
                    </div>
                    <div class="status">
                        <p class="state">Statut: Terminé</p>
                        <div class="state_action">
                            <img src="./assets/images/mark_check.svg" alt="Icon de marque page">
                            <p class="purple">Dans la loge</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="header">
                <p class="tag movie">FILM</p>
                <div class="element_status finished">
                    <div class="dot"></div>
                    <p>TERMINÉ</p>
                </div>
            </div>
            <div class="overlay_container">
                <img class="cover" src="assets/images/content/dune_2_cover.jpg" alt="Image de couverture de dune">
            </div>
            <div class="footer">
                <p class="element_infos">
                    2024 • 2h 46m
                </p>
                <div class="information_container">
                    <h2>Dune: Deuxième Partie</h2>
                    <p class="description">Paul Atreides s'unit à Chani et aux Fremen tout en préparant sa revanche contre les conspirateurs qui ont…</p>
                </div>
                <div class="status_container">
                    <div class="note">
                        <p>EVALUATION :</p>
                        <div class="star_container">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 1/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 2/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 3/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 4/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 5/5">
                        </div>
                    </div>
                    <div class="status">
                        <p class="state">Statut: Terminé</p>
                        <div class="state_action">
                            <img src="./assets/images/pen.svg" alt="Icon de crayon">
                            <p>Éditer statut</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="header">
                <p class="tag movie">FILM</p>
                <div class="element_status discover">
                    <div class="dot"></div>
                    <p>À DÉCOUVRIR</p>
                </div>
            </div>
            <div class="overlay_container">
                <img class="cover" src="./assets/images/content/interstellar_cover.jpg" alt="Image de couverture du film interstellar">
            </div>
            <div class="footer">
                <p class="element_infos">
                    2014 • 2h 49m
                </p>
                <div class="information_container">
                    <h2>Interstellar</h2>
                    <p class="description">Une équipe d'explorateurs franchit un trou de ver récemment découvert afin de surpasser les limites du…</p>
                </div>
                <div class="status_container">
                    <div class="note">
                        <p>EVALUATION :</p>
                        <div class="star_container">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 1/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 2/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 3/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 4/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 5/5">
                        </div>
                    </div>
                    <div class="status">
                        <p class="state">Statut: Terminé</p>
                        <div class="state_action to_add">
                            <img src="./assets/images/plus.svg" alt="Icon de plus">
                            <p>Ajouter</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="header">
                <p class="tag serie">SÉRIE</p>
                <div class="element_status ongoing">
                    <div class="dot"></div>
                    <p>EN COURS</p>
                </div>
            </div>
            <div class="overlay_container">
                <img class="cover" src="./assets/images/content/arcane_cover.jpg" alt="Image de couverture de Arcane">
            </div>
            <div class="footer">
                <p class="element_infos">
                    2021 • 2 Saisons
                </p>
                <div class="information_container">
                    <h2>Arcane</h2>
                    <p class="description">Au cœur du conflit entre les cités jumelles de Piltover et de Zaun, deux sœurs se battent dans des camps opposés</p>
                </div>
                <div class="status_container">
                    <div class="note">
                        <p>EVALUATION :</p>
                        <div class="star_container">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 1/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 2/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 3/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 4/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 5/5">
                        </div>
                    </div>
                    <div class="status">
                        <p class="state">Statut: Terminé</p>
                        <div class="state_action">
                            <img src="./assets/images/rewind.svg" alt="Icon de retours en arrière">
                            <p>Épisode S2E03</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="header">
                <p class="tag serie">SÉRIE</p>
                <div class="element_status finished">
                    <div class="dot"></div>
                    <p>TERMINÉ</p>
                </div>
            </div>
            <div class="overlay_container">
                <img class="cover" src="./assets/images/content/the_last_of_us_cover.jpg" alt="Image de couverture de The last of us">
            </div>
            <div class="footer">
                <p class="element_infos">
                    2023 • 9 Épisodes
                </p>
                <div class="information_container">
                    <h2>The Last of Us</h2>
                    <p class="description">Quand le monde s'effondre sous l'emprise d'un champignon parasite muté, un survivant endurci prend en</p>
                </div>
                <div class="status_container">
                    <div class="note">
                        <p>EVALUATION :</p>
                        <div class="star_container">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 1/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 2/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 3/5">
                            <img src="./assets/images/star.svg" alt="Etoile de notation 4/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 5/5">
                        </div>
                    </div>
                    <div class="status">
                        <p class="state">Statut: En cours</p>
                        <div class="state_action">
                            <img src="./assets/images/check_list.svg" alt="Icon de crayon">
                            <p>Revue écrite</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="header">
                <p class="tag serie">SÉRIE</p>
                <div class="element_status discover">
                    <div class="dot"></div>
                    <p>À DÉCOUVRIR</p>
                </div>
            </div>
            <div class="overlay_container">
                <img class="cover" src="./assets/images/content/severance_cover.jpg" alt="Image de couverture de la série Severance">
            </div>
            <div class="footer">
                <p class="element_infos">
                    2022 • 1 Saison
                </p>
                <div class="information_container">
                    <h2>Severance</h2>
                    <p class="description">Mark mène une équipe dont les employés ont subi une procédure chirurgicale séparant leurs souvenirs…</p>
                </div>
                <div class="status_container">
                    <div class="note">
                        <p>EVALUATION :</p>
                        <div class="star_container">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 1/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 2/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 3/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 4/5">
                            <img src="./assets/images/star_empty.svg" alt="Etoile de notation 5/5">
                        </div>
                    </div>
                    <div class="status">
                        <p class="state">Statut: À découvrir</p>
                        <div class="state_action to_add">
                            <img src="./assets/images/plus.svg" alt="Icon de plus">
                            <p>Ajouter</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section>
        <div class="banner">

            <div>
                <p class="banner_texte">
                    <img class="banner_icon" width="14.67px" height="14px" src="./assets/icon/Icon.svg" alt="Logo de Vault">
                    ARCHITECTURE WEB SPRINT 1 & 4

                </p>
                <p class="banner_texte1"> Progression de votre Archivage Pop-Culture

                </p>
                <p class="banner_texte2">
                <p>Chaque œuvre annotée enrichit votre rang et consolide la persistance locale de vos
                    trésors culturels.
                </p>


                <div class="banner_text6">
                    <div>
                        <p class="banner_text3">6/9

                        </p>
                        <P class="banner_text4">
                            ŒUVRES AMORCÉES
                        </P>
                    </div>
                    <div>
                        <p class="banner_text5">4.8 / 5</p>
                    </div>

                </div>

            </div>
            <div>

            </div>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/templates/footer.php'; ?>
</body>
</html>