<?php
// Chemins du jeu adaptés à la racine du site et à ses sous-dossiers.
require_once __DIR__ . '/../../auth.php';
$fireAssetUrl = static fn(string $path): string => htmlspecialchars(authUrl('assets/games/firefighter/' . $path), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fireChallengeApi = htmlspecialchars(authUrl('includes/actions/fire_challenge_action.php'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fireChallengeToken = htmlspecialchars(csrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fireChallengeUser = '0';
if (currentUser() !== null) {
    $fireChallengeUser = '1';
}
?>
<link rel="stylesheet" href="<?= $fireAssetUrl('game.css') ?>">

<button type="button" class="fire-launcher" data-fire-open hidden aria-haspopup="dialog" aria-controls="fire-game">Rejouer à Intervention</button>

<dialog id="fire-game" class="fire-game" data-state="home" data-phase="arrival" data-assets="<?= $fireAssetUrl('') ?>" data-challenge-api="<?= $fireChallengeApi ?>" data-csrf-token="<?= $fireChallengeToken ?>" data-challenge-user="<?= $fireChallengeUser ?>" aria-labelledby="fire-title">
    <canvas id="fire-canvas" width="1440" height="900" tabindex="0" aria-label="Terrain du jeu. Flèches ou ZQSD pour se déplacer, E pour agir, souris pour viser, clic gauche ou Espace pour arroser. Échap ouvre le menu." aria-describedby="fire-menu-controls">Votre navigateur ne peut pas afficher le terrain de jeu.</canvas>

    <!-- Le terrain reste libre ; seuls les choix interrompent l'action. -->
    <section id="fire-home" class="fire-home fire-overlay" aria-labelledby="fire-title">
        <div class="fire-home-copy">
            <span class="fire-home-mark" aria-hidden="true"><svg viewBox="0 0 32 32" shape-rendering="crispEdges"><path d="M14 2h6v8h4v6h4v10h-4v4H8v-4H4V16h4v-6h4v10h4v-8h-2Z" fill="currentColor"/><path d="M14 20h6v6h-6Z" fill="#fff1b8"/></svg></span>
            <h1 id="fire-title">INTERVENTION</h1>
            <p class="fire-home-subtitle">Feu d’habitation</p>
            <div class="fire-guard-launch">
                <button type="button" id="fire-daily" class="fire-button fire-button-primary">Garde du jour <span aria-hidden="true">▶</span></button>
                <p id="fire-guard-info" class="fire-guard-info">Une même intervention à rejouer aujourd’hui. Record par binôme.</p>
                <button type="button" id="fire-random" class="fire-button fire-button-light">Nouvelle garde</button>
                <button type="button" id="fire-start" class="fire-button fire-training">Exercice classique</button>
                <button type="button" id="fire-challenge-open" class="fire-button fire-button-cyan">Défi 10 secondes</button>
            </div>
        </div>
    </section>

    <!-- Le défi conserve son propre chrono, indépendant de l'intervention. -->
    <section id="fire-challenge" class="fire-challenge fire-overlay" data-phase="idle" aria-labelledby="fire-challenge-title" hidden>
        <div class="fire-challenge-shell">
            <div class="fire-challenge-heading">
                <p class="fire-eyebrow">ENTRAÎNEMENT À LA CASERNE</p>
                <h2 id="fire-challenge-title">DÉFI 10 SECONDES</h2>
                <p id="fire-challenge-help">Cliquez sur le feu ou utilisez Entrée / Espace. Chaque activation éteint un feu et rapporte un point.</p>
            </div>
            <dl class="fire-challenge-hud">
                <div><dt>FEUX ÉTEINTS</dt><dd><output id="fire-challenge-score" aria-live="off">0</output></dd></div>
                <div><dt>TEMPS RESTANT</dt><dd><output id="fire-challenge-time" aria-live="off">10.0</output><span> s</span></dd></div>
            </dl>
            <div id="fire-challenge-arena" class="fire-challenge-arena" aria-label="Zone d’entraînement">
                <p id="fire-challenge-arena-hint" class="fire-challenge-arena-hint">Appuyez sur Démarrer pour commencer.</p>
                <button type="button" id="fire-challenge-target" aria-describedby="fire-challenge-help" disabled hidden><svg viewBox="0 0 32 32" shape-rendering="crispEdges" aria-hidden="true"><path d="M14 2h6v8h4v6h4v10h-4v4H8v-4H4V16h4v-6h4v10h4v-8h-2Z" fill="#ff9139"/><path d="M14 20h6v6h-6Z" fill="#fff1b8"/></svg><span>Éteindre</span></button>
            </div>
            <div id="fire-challenge-result" class="fire-challenge-result" hidden>
                <p>Score final : <strong id="fire-challenge-final-score">0</strong> feux éteints.</p>
                <div class="fire-challenge-result-buttons">
                    <button type="button" id="fire-challenge-save" class="fire-button fire-button-cyan" hidden>Sauvegarder mon score</button>
                    <button type="button" id="fire-challenge-replay" class="fire-button fire-button-light">Rejouer</button>
                </div>
                <p id="fire-challenge-login" class="fire-challenge-login" hidden><a href="<?= htmlspecialchars(authUrl('login.php'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Connectez-vous avant votre prochain défi</a> pour pouvoir sauvegarder son score.</p>
            </div>
            <div class="fire-challenge-controls">
                <button type="button" id="fire-challenge-start" class="fire-button fire-button-primary">Démarrer</button>
                <button type="button" class="fire-button fire-button-light" data-fire-home>Retour caserne</button>
            </div>
            <p id="fire-challenge-message" class="fire-challenge-message" role="status" aria-live="polite" aria-atomic="true"></p>
            <p class="fire-challenge-clock-note">Les 10 secondes continuent pendant le menu Échap.</p>
        </div>
    </section>

    <section id="fire-intro" class="fire-intro fire-overlay" aria-labelledby="fire-intro-label" hidden>
        <img id="fire-photo-one" class="fire-intro-photo" src="<?= $fireAssetUrl('station.jpg') ?>" alt="Fourgon pompe-tonne photographié par Anthony Levrot, vue avant gauche." loading="lazy" decoding="async">
        <img id="fire-photo-two" class="fire-intro-photo" src="<?= $fireAssetUrl('departure.jpg') ?>" alt="Fourgon pompe-tonne photographié par Anthony Levrot, vue avant droite." loading="lazy" decoding="async">
        <div class="fire-photo-shade" aria-hidden="true"></div>
        <div class="fire-intro-copy">
            <p id="fire-intro-label" class="fire-eyebrow">LE DÉPART</p>
            <h2 id="fire-intro-caption">L’équipage entre en action.</h2>
            <p id="fire-intro-credit" class="fire-photo-credit">Photo : <a data-fire-credit-photo="current" href="https://commons.wikimedia.org/wiki/File:Langon_-_33210_-_2026.07.10_-_SDIS_33_-_Fourgon_Pompe_Tonne_(vue_avant_gauche)_%C2%A9_Anthony_Levrot.jpg" target="_blank" rel="noopener noreferrer">Anthony Levrot · Wikimedia Commons</a> · <a data-fire-credit-license href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-SA 4.0</a></p>
        </div>
        <button type="button" id="fire-skip" class="fire-button fire-button-light">Passer la séquence <span aria-hidden="true">▶</span></button>
    </section>

    <section id="fire-orders" class="fire-orders fire-overlay" aria-labelledby="fire-orders-title" hidden>
        <div class="fire-orders-window">
            <p id="fire-duty-label" class="fire-eyebrow">CHEF D’AGRÈS / ORDRES</p>
            <h2 id="fire-orders-title">CHOISIS TON BINÔME</h2>
            <p class="fire-orders-subtitle">L’autre binôme intervient à tes côtés.</p>
            <article class="fire-team-card">
                <h3><span class="fire-team-code">BAT</span> Binôme d’attaque</h3>
                <p id="fire-order-bat">Établis ta lance sur la DAT, avec une LAT de 3 × 20 m, puis reconnais le rez-de-chaussée.</p>
                <button type="button" class="fire-button fire-button-primary" data-fire-team="BAT" aria-describedby="fire-order-bat">Prendre le BAT <span aria-hidden="true">▶</span></button>
                <p class="fire-role-best" data-fire-best="BAT" hidden></p>
            </article>
            <article class="fire-team-card">
                <h3><span class="fire-team-code fire-team-code-cyan">BAL</span> Binôme d’alimentation</h3>
                <p id="fire-order-bal">Établis la DAT au pied de l’habitation et alimente le BAT. Sur ordre du chef, reconnais le deuxième étage par l’échelle à coulisse.</p>
                <button type="button" class="fire-button fire-button-cyan" data-fire-team="BAL" aria-describedby="fire-order-bal">Prendre le BAL <span aria-hidden="true">▶</span></button>
                <p class="fire-role-best" data-fire-best="BAL" hidden></p>
            </article>
        </div>
    </section>

    <!-- Toutes les commandes générales sont regroupées dans le menu Échap. -->
    <section id="fire-paused" class="fire-menu fire-overlay" aria-labelledby="fire-menu-title" hidden>
        <div class="fire-menu-window">
            <p class="fire-eyebrow">INTERVENTION</p>
            <h2 id="fire-menu-title">MENU</h2>
            <div class="fire-menu-buttons">
                <button type="button" id="fire-resume" class="fire-button fire-button-primary">Reprendre <span aria-hidden="true">▶</span></button>
                <button type="button" id="fire-restart" class="fire-button fire-button-light">Recommencer</button>
                <button type="button" id="fire-sound" class="fire-button fire-button-light" aria-label="Activer le son" aria-pressed="false" title="Activer le son"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m11 5-6 4H2v6h3l6 4V5Z"/><path d="m16 9 5 6m0-6-5 6"/></svg><span data-fire-label>Son coupé</span></button>
                <button type="button" id="fire-close" class="fire-button fire-button-quit">Quitter</button>
            </div>
            <p id="fire-menu-controls" class="fire-menu-controls"><kbd>Flèches</kbd> ou <kbd>ZQSD</kbd> : déplacement<br><kbd>E</kbd> : agir près d’un repère<br>Souris : viser · Clic ou <kbd>Espace</kbd> : arroser<br>LAT : 3 tuyaux de 20 m, soit 60 m maximum. Le tuyau suit le binôme au retour.<br><kbd>Échap</kbd> : menu / reprendre</p>
            <p id="fire-challenge-menu-note" class="fire-challenge-menu-note" hidden>Défi : les 10 secondes continuent pendant ce menu.</p>
            <details class="fire-credits">
                <summary>Crédits et licences</summary>
                <div>
                    <p>Photos : Anthony Levrot, Wikimedia Commons, <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-SA 4.0</a>.</p>
                    <p><a href="https://commons.wikimedia.org/wiki/File:Langon_-_33210_-_2026.07.10_-_SDIS_33_-_Fourgon_Pompe_Tonne_(vue_avant_gauche)_%C2%A9_Anthony_Levrot.jpg" target="_blank" rel="noopener noreferrer">Vue avant gauche</a> · <a href="https://commons.wikimedia.org/wiki/File:Langon_-_33210_-_2026.07.10_-_SDIS_33_-_Fourgon_Pompe_Tonne_(vue_avant_droit)_%C2%A9_Anthony_Levrot.jpg" target="_blank" rel="noopener noreferrer">Vue avant droite</a></p>
                    <p>Affichage recadré et animé ; photos non retouchées.</p>
                    <p>Flammes : <a href="https://opengameart.org/content/animated-fire" target="_blank" rel="noopener noreferrer">BenHickling, Animated Fire</a> · <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="noopener noreferrer">CC0</a>.</p>
                    <p>Jeu indépendant, sans affiliation à un service d’incendie.</p>
                </div>
            </details>
        </div>
    </section>

    <section id="fire-result" class="fire-result fire-overlay" aria-labelledby="fire-result-title" hidden>
        <div class="fire-result-window">
            <p class="fire-eyebrow">FIN D’INTERVENTION</p>
            <h2 id="fire-result-title">MISSION ACCOMPLIE</h2>
            <p id="fire-result-text">L’équipe a terminé son intervention.</p>
            <div id="fire-result-duty" class="fire-report" hidden>
                <p id="fire-report-name" class="fire-eyebrow"></p>
                <p id="fire-report-time" class="fire-report-time"></p>
                <p id="fire-report-crew"></p>
                <p id="fire-report-best"></p>
            </div>
            <button type="button" id="fire-result-replay" class="fire-button fire-button-primary">Recommencer <span aria-hidden="true">▶</span></button>
            <button type="button" id="fire-result-next" class="fire-button fire-button-light" hidden>Nouvelle garde</button>
            <button type="button" class="fire-button fire-button-light" data-fire-quit>Quitter</button>
        </div>
    </section>

    <button type="button" class="fire-menu-hint" data-fire-pause aria-controls="fire-paused" aria-label="Ouvrir le menu du jeu">Échap · Menu</button>
    <div class="fire-touch" role="group" aria-label="Commandes tactiles">
        <div class="fire-dpad">
            <button type="button" data-fire-move="up" aria-label="Avancer vers le haut">↑</button>
            <button type="button" data-fire-move="left" aria-label="Aller à gauche">←</button>
            <button type="button" data-fire-move="down" aria-label="Avancer vers le bas">↓</button>
            <button type="button" data-fire-move="right" aria-label="Aller à droite">→</button>
        </div>
        <div class="fire-touch-actions">
            <button type="button" data-fire-interact>AGIR</button>
            <button type="button" class="fire-spray-button" data-fire-spray>ARROSER</button>
        </div>
    </div>
    <p id="fire-notice" class="fire-notice" role="status" aria-live="polite" aria-atomic="true" hidden></p>
    <div id="fire-dialogue-live" class="fire-sr-only" role="status" aria-live="polite" aria-atomic="true"></div>
</dialog>

<script type="module" src="<?= $fireAssetUrl('game.js') ?>"></script>
