<?php
// Chemins du jeu adaptés à la racine du site et à ses sous-dossiers.
require_once __DIR__ . '/../../auth.php';
$fireAssetUrl = static fn(string $path): string => htmlspecialchars(authUrl('assets/games/firefighter/' . $path), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<link rel="stylesheet" href="<?= $fireAssetUrl('game.css') ?>">

<button type="button" class="fire-launcher" data-fire-open hidden aria-haspopup="dialog" aria-controls="fire-game">Rejouer à Incendie à Vault !</button>

<dialog id="fire-game" class="fire-game" data-state="playing" data-phase="mission" data-assets="<?= $fireAssetUrl('') ?>" aria-labelledby="fire-title">
    <h1 id="fire-title" class="fire-sr-only">Incendie à Vault !</h1>
    <canvas id="fire-canvas" width="1440" height="900" tabindex="0" aria-label="Terrain du jeu. Flèches ou ZQSD pour se déplacer, E pour agir, souris pour viser, clic gauche ou Espace pour arroser. Échap ouvre le menu." aria-describedby="fire-menu-controls">Votre navigateur ne peut pas afficher le terrain de jeu.</canvas>

    <!-- Le menu met la partie en pause sans quitter le terrain. -->
    <section id="fire-paused" class="fire-menu fire-overlay" aria-labelledby="fire-menu-title" hidden>
        <div class="fire-menu-window">
            <div class="fire-menu-heading">
                <div>
                    <p class="fire-eyebrow">INCENDIE À VAULT !</p>
                    <h2 id="fire-menu-title">Pause</h2>
                </div>
                <span class="fire-menu-key" aria-hidden="true">ÉCHAP</span>
            </div>

            <div class="fire-menu-body">
                <div class="fire-menu-buttons">
                    <button type="button" id="fire-resume" class="fire-button fire-button-primary"><span data-fire-label>Reprendre</span><span class="fire-menu-arrow" aria-hidden="true">→</span></button>
                    <button type="button" id="fire-restart" class="fire-button fire-button-light">Recommencer<span class="fire-menu-arrow" aria-hidden="true">↻</span></button>
                    <button type="button" id="fire-close" class="fire-button fire-button-quit">Quitter le jeu<span class="fire-menu-arrow" aria-hidden="true">↗</span></button>
                </div>

                <fieldset class="fire-menu-modes">
                    <legend>Changer de binôme</legend>
                    <div class="fire-team-buttons">
                        <button type="button" id="fire-start" class="fire-button fire-training"><strong>BAT</strong><span>Rez-de-chaussée</span></button>
                        <button type="button" id="fire-bal" class="fire-button fire-button-light"><strong>BAL</strong><span>Premier étage</span></button>
                    </div>
                </fieldset>
            </div>

            <div class="fire-menu-help">
                <details class="fire-controls">
                    <summary>Commandes</summary>
                    <div id="fire-menu-controls" class="fire-menu-controls">
                        <dl>
                            <dt>Se déplacer</dt><dd><kbd>Flèches</kbd> / <kbd>ZQSD</kbd></dd>
                            <dt>Agir</dt><dd><kbd>E</kbd> près d’un repère</dd>
                            <dt>Viser</dt><dd>Souris</dd>
                            <dt>Arroser</dt><dd>Clic / <kbd>Espace</kbd></dd>
                            <dt>Reprendre</dt><dd><kbd>Échap</kbd></dd>
                        </dl>
                        <p>LAT : 3 tuyaux de 20 m, soit 60 m maximum. Le tuyau suit le binôme au retour.</p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    <section id="fire-result" class="fire-result fire-overlay" aria-labelledby="fire-result-title" aria-describedby="fire-result-text" hidden>
        <div class="fire-result-window">
            <div class="fire-result-heading">
                <p class="fire-eyebrow">INCENDIE À VAULT !</p>
                <h2 id="fire-result-title">Feu maîtrisé</h2>
            </div>
            <p id="fire-result-text">Tout le monde est à l’abri. L’intervention est terminée.</p>
            <div class="fire-result-actions">
                <button type="button" id="fire-result-replay" class="fire-button fire-button-primary"><span data-fire-label>Rejouer</span><span class="fire-result-arrow" aria-hidden="true">↻</span></button>
                <button type="button" class="fire-button fire-button-quit" data-fire-quit>Quitter le jeu<span class="fire-result-arrow" aria-hidden="true">↗</span></button>
            </div>
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
