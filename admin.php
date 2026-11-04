<?php
// Administration : accès réservé au rôle admin.
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Vault - Admin</title>
    <link rel="stylesheet" href="./assets/css/reset.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/pages/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin_page">

    <aside class="admin_sidemenu">
        <nav class="navigation" aria-label="Administration">
            <h2>
                <a href="index.php">
                    <img class="brand_logo" src="./assets/images/VAULT Logo.png" alt="">
                    VAULT
                    <img class="brand_dot" src="./assets/images/point violet.png" alt="">
                </a>
            </h2>

            <p>SUPERVISION &amp; CATALOGUE</p>

            <ul>
                <li>
                    <a class="nav_active" href="admin.php" aria-current="page">
                        <img src="./assets/images/Icon admin 1.png" alt="">
                        Console Générale
                    </a>
                </li>
                <li>
                    <a href="#record_form">
                        <img src="./assets/images/Icon admin 2.png" alt="">
                        Gestion Œuvres
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <img src="./assets/images/Icon admin 3.png" alt="">
                        Comptes &amp; XP
                    </a>
                </li>
            </ul>
        </nav>

        <div class="system_card">
            <p class="system_title">SYSTÈME ACTIF <span class="status_dot" aria-hidden="true"></span></p>
            <p class="system_text">Serveur de données connecté</p>
            <div class="system_progress" aria-hidden="true"></div>
        </div>
    </aside>

    <main class="top_bar">

        <div class="console">
            <div class="console_title">
                <span class="console_mode">MODE ADMINISTRATEUR</span>
                <span class="console_separator">//</span>
                <h1>Console Système</h1>
            </div>

            <div class="console_account">
                <div class="console_experience">
                    <img class="level_indicator" src="./assets/images/level-indicator.svg" alt="">
                    <span class="console_level">Niv. 2</span>
                    <span class="console_dot">•</span>
                    <span>135 XP</span>
                </div>
                <a class="console_profile" href="profile.php" aria-label="Mon profil">
                    <img class="console_avatar" src="./assets/images/profil-picture.jpg" alt="">
                    <img class="console_arrow" src="./assets/images/arrow-down.svg" alt="">
                </a>
            </div>
        </div>

        <div class="dashboard">

            <section class="admin_banner" aria-labelledby="dashboard_title">
                <div>
                    <h2 id="dashboard_title"><span class="title_dot">•</span> Console d’Administration du Catalogue</h2>
                    <p class="banner_subtitle">
                        <img class="small_icon" src="./assets/images/coche_valider.png" alt="">
                        Gestion centralisée du catalogue et contrôle des fiches médias
                    </p>
                </div>
                <div class="banner_actions">
                    <span class="admin_badge">
                        <img class="small_icon" src="./assets/images/coche_valider 2.png" alt="">
                        MODE ADMINISTRATEUR (RÔLE : ADMIN)
                    </span>
                    <button class="new_button" type="button">
                        <img class="small_icon" src="./assets/images/plus_entouré.png" alt="">
                        + Nouvelle fiche
                    </button>
                </div>
            </section>

            <div class="dashboard_content">

                <!-- Formulaire de démonstration, sans sauvegarde reliée. -->
                <form class="record_form" id="record_form" aria-labelledby="form_title">
                    <div class="form_tabs">
                        <button type="button">⊕ Créer une œuvre</button>
                        <button class="tab_active" type="button">✎ Modifier #101</button>
                    </div>

                    <div class="form_heading">
                        <h3 id="form_title">
                            <img class="document_icon" src="./assets/images/fichier.png" alt="">
                            Fiche de Référencement
                        </h3>
                        <span class="edition_badge">ÉDITION #101</span>
                    </div>

                    <div class="form_field">
                        <div class="field_heading">
                            <label for="record_name">Intitulé de l’œuvre</label>
                            <small>1 à 150 car.</small>
                        </div>
                        <input id="record_name" name="record_name" type="text" value="Blade Runner 2049" maxlength="150" required>
                    </div>

                    <fieldset class="form_field media_field">
                        <legend>Type de média</legend>
                        <div class="media_choices">
                            <input id="media_film" name="media_type" type="radio" value="film" checked>
                            <label for="media_film">Film</label>
                            <input id="media_series" name="media_type" type="radio" value="series">
                            <label for="media_series">Série</label>
                            <input id="media_game" name="media_type" type="radio" value="game">
                            <label for="media_game">Jeu vidéo</label>
                        </div>
                    </fieldset>

                    <div class="field_row">
                        <div class="form_field">
                            <label class="field_label" for="release_year">Année de sortie</label>
                            <input class="year_input" id="release_year" name="release_year" type="number" value="2017">
                        </div>
                        <div class="form_field">
                            <label class="field_label" for="initial_status">Statut initial</label>
                            <select class="status_select" id="initial_status" name="initial_status">
                                <option value="discover">À DÉCOUVRIR</option>
                                <option value="in_progress">EN COURS</option>
                                <option value="finished">TERMINÉ</option>
                            </select>
                        </div>
                    </div>

                    <div class="form_field">
                        <div class="field_heading">
                            <label for="poster_file">Visuel de l’affiche (format 2:3)</label>
                            <small>PNG, JPG, WebP</small>
                        </div>
                        <div class="upload_zone">
                            <img class="upload_icon" src="./assets/images/fichier_import.png" alt="">
                            <p><span class="upload_link">Choisir un fichier</span> ou glisser-déposer</p>
                            <p class="upload_hint" id="poster_hint">Ratio 2:3 recommandé • Max 5 Mo</p>
                            <input id="poster_file" name="poster_file" type="file" accept="image/png,image/jpeg,image/webp" aria-describedby="poster_hint">
                        </div>
                    </div>

                    <div class="form_field synopsis_field">
                        <div class="field_heading">
                            <label for="record_synopsis">Synopsis / Description</label>
                            <small>600 car. max.</small>
                        </div>
                        <textarea id="record_synopsis" name="record_synopsis" maxlength="600">Denis Villeneuve • Enquête néo-noire réplicante à Los Angeles</textarea>
                    </div>

                    <div class="form_actions">
                        <button class="cancel_button" type="reset">
                            <img class="small_icon" src="./assets/images/fleche_retour.png" alt="">
                            <span>Annuler<br>l’édition</span>
                        </button>
                        <button class="update_button" type="button">
                            <img class="small_icon" src="./assets/images/actualiser.png" alt="">
                            <span>Mettre à jour la<br>fiche #101</span>
                        </button>
                    </div>
                </form>

                <!-- Statistiques de démonstration. -->
                <section class="catalog_stats" aria-label="Statistiques du catalogue">
                    <div class="stats_cards">
                        <article class="stat_card">
                            <div class="stat_heading">
                                <h3>VOLUME CATALOGUE</h3>
                                <img class="small_icon" src="./assets/images/media.png" alt="">
                            </div>
                            <p class="stat_number"><strong>12</strong> <span>œuvres</span></p>
                            <p class="stat_detail"><span class="purple_text">+3</span> ce cycle</p>
                        </article>
                        <article class="stat_card">
                            <div class="stat_heading">
                                <h3>CINÉMA &amp; SÉRIES</h3>
                                <img class="small_icon" src="./assets/images/masque.png" alt="">
                            </div>
                            <p class="stat_number"><strong class="purple_text">8</strong> <span>entrées</span></p>
                            <p class="stat_detail">66.7% du corpus</p>
                        </article>
                        <article class="stat_card">
                            <div class="stat_heading">
                                <h3>JEUX VIDÉO</h3>
                                <img class="small_icon" src="./assets/images/manette.png" alt="">
                            </div>
                            <p class="stat_number"><strong class="orange_text">4</strong> <span>titres</span></p>
                            <p class="stat_detail">33.3% du corpus</p>
                        </article>
                    </div>

                    <article class="distribution_card">
                        <div class="distribution_heading">
                            <h3>Répartition Thématique du Catalogue</h3>
                            <span>Total 100% normalisé</span>
                        </div>
                        <img class="distribution_bar" src="./assets/images/bar_%25.png" alt="">
                        <ul class="distribution_legend">
                            <li><span class="legend_dot scifi_dot" aria-hidden="true"></span>Sci-Fi (42%)</li>
                            <li><span class="legend_dot cyberpunk_dot" aria-hidden="true"></span>Cyberpunk (25%)</li>
                            <li><span class="legend_dot fantasy_dot" aria-hidden="true"></span>Fantasy / Anim (20%)</li>
                            <li><span class="legend_dot other_dot" aria-hidden="true"></span>Autres (13%)</li>
                        </ul>
                    </article>
                </section>
            </div>

            <!-- Répertoire des fiches de démonstration. -->
            <section class="records_section" aria-labelledby="records_title">
                <div class="records_toolbar">
                    <div class="records_heading">
                        <img class="records_database" src="./assets/images/bdd-icon.svg" alt="">
                        <h2 id="records_title">Répertoire Centralisé &amp; Contrôle des Entrées</h2>
                        <span class="records_count">12 Fiches</span>
                    </div>
                    <div class="records_tools">
                        <a class="records_add" href="#record_form">
                            <span aria-hidden="true">＋</span>
                            Ajouter une fiche
                        </a>
                        <label class="records_search">
                            <img src="./assets/images/magnifying-glass.svg" alt="">
                            <input type="search" placeholder="Filtrer par titre ou tag..." aria-label="Filtrer les fiches par titre ou catégorie">
                        </label>
                        <button class="records_refresh" type="button" aria-label="Actualiser le répertoire">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M20 4v6h-6M4 20v-6h6"></path>
                                <path d="M5.6 8a7 7 0 0 1 11.5-3L20 10M4 14l2.9 5A7 7 0 0 0 18.4 16"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="records_scroll" tabindex="0" role="region" aria-label="Répertoire des œuvres, défilement horizontal disponible">
                    <table class="records_table" aria-labelledby="records_title">
                        <colgroup>
                            <col class="records_col_id">
                            <col class="records_col_visual">
                            <col class="records_col_title">
                            <col class="records_col_category">
                            <col class="records_col_year">
                            <col class="records_col_collections">
                            <col class="records_col_actions">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Visuel</th>
                                <th scope="col">Titre &amp; notice</th>
                                <th scope="col">Catégorie</th>
                                <th scope="col">Sortie</th>
                                <th scope="col">Collections<br>liées</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="records_row records_row_active">
                                <th scope="row" class="records_id"><span class="records_active_dot" aria-hidden="true">•</span> #101</th>
                                <td><img class="records_poster" src="./assets/images/content/blade_runner_cover.jpg" alt="Affiche de Blade Runner 2049"></td>
                                <td>
                                    <div class="records_name"><strong>Blade Runner 2049</strong><span class="records_editing">En cours d’édition</span></div>
                                    <p class="records_description">Denis Villeneuve • Enquête néo-noire réplicante à Los Angeles</p>
                                </td>
                                <td><span class="records_category">Film</span></td>
                                <td class="records_year">2017</td>
                                <td class="records_collections"><strong>42</strong><span>utilisateurs</span></td>
                                <td class="records_action">
                                    <a class="records_edit records_edit_selected" href="#record_form" aria-label="Modifier Blade Runner 2049">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m16 3 5 5M4 15 16 3l5 5L9 20l-6 1z"></path></svg>
                                        <span>Édition</span>
                                    </a>
                                </td>
                            </tr>
                            <tr class="records_row">
                                <th scope="row" class="records_id">#102</th>
                                <td><img class="records_poster" src="./assets/images/content/cyberpunk_phantom_liberty_cover.jpg" alt="Affiche de Cyberpunk 2077 : Phantom Liberty"></td>
                                <td>
                                    <div class="records_name"><strong>Cyberpunk 2077: Phantom Liberty</strong></div>
                                    <p class="records_description">CD Projekt Red • Thriller d’espionnage technologique à Dogtown</p>
                                </td>
                                <td><span class="records_category records_category_game">Jeu<br>vidéo</span></td>
                                <td class="records_year">2023</td>
                                <td class="records_collections"><strong>38</strong><span>utilisateurs</span></td>
                                <td class="records_action">
                                    <a class="records_edit" href="#record_form" aria-label="Modifier Cyberpunk 2077 : Phantom Liberty">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m16 3 5 5M4 15 16 3l5 5L9 20l-6 1z"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            <tr class="records_row">
                                <th scope="row" class="records_id">#103</th>
                                <td><img class="records_poster" src="./assets/images/content/arcane_cover.jpg" alt="Affiche de Arcane : League of Legends"></td>
                                <td>
                                    <div class="records_name"><strong>Arcane: League of Legends</strong></div>
                                    <p class="records_description">Fortiche Production • Tensions géopolitiques entre Piltover et Zaun</p>
                                </td>
                                <td><span class="records_category">Série</span></td>
                                <td class="records_year">2021</td>
                                <td class="records_collections"><strong>51</strong><span>utilisateurs</span></td>
                                <td class="records_action">
                                    <a class="records_edit" href="#record_form" aria-label="Modifier Arcane : League of Legends">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m16 3 5 5M4 15 16 3l5 5L9 20l-6 1z"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            <tr class="records_row">
                                <th scope="row" class="records_id">#104</th>
                                <td><img class="records_poster" src="./assets/images/content/dune_2_cover.jpg" alt="Affiche de Dune : Part Two"></td>
                                <td>
                                    <div class="records_name"><strong>Dune: Part Two</strong></div>
                                    <p class="records_description">Denis Villeneuve • L’ascension messianique de Paul Atréides sur Arrakis</p>
                                </td>
                                <td><span class="records_category">Film</span></td>
                                <td class="records_year">2024</td>
                                <td class="records_collections"><strong>33</strong><span>utilisateurs</span></td>
                                <td class="records_action">
                                    <a class="records_edit" href="#record_form" aria-label="Modifier Dune : Part Two">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m16 3 5 5M4 15 16 3l5 5L9 20l-6 1z"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            <tr class="records_row">
                                <th scope="row" class="records_id">#105</th>
                                <td><img class="records_poster" src="./assets/images/content/elden_ring_erdtree_cover.jpg" alt="Affiche de Elden Ring : Shadow of the Erdtree"></td>
                                <td>
                                    <div class="records_name"><strong>Elden Ring: Shadow of the Erdtree</strong></div>
                                    <p class="records_description">FromSoftware • Épopée crépusculaire dans le Royaume des Ombres</p>
                                </td>
                                <td><span class="records_category records_category_game">Jeu<br>vidéo</span></td>
                                <td class="records_year">2024</td>
                                <td class="records_collections"><strong>29</strong><span>utilisateurs</span></td>
                                <td class="records_action">
                                    <a class="records_edit" href="#record_form" aria-label="Modifier Elden Ring : Shadow of the Erdtree">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m16 3 5 5M4 15 16 3l5 5L9 20l-6 1z"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="records_footer">
                    <p class="records_summary">Affichage : 5 / 12 fiches primaires</p>
                    <nav class="records_pagination" aria-label="Pagination du répertoire">
                        <button type="button" aria-label="Page précédente" disabled>Prev</button>
                        <button class="records_page_active" type="button" aria-label="Page 1" aria-current="page">1</button>
                        <button type="button" aria-label="Page 2">2</button>
                        <button type="button" aria-label="Page 3">3</button>
                        <button type="button" aria-label="Page suivante">Next</button>
                    </nav>
                </div>
            </section>
        </div>
    </main>
</body>
</html>