<?php
// En-tête commun : liens adapté à la session et à la page active.
require_once __DIR__ . '/../../auth.php';
startSession();
$headerUser = currentUser();
$current_page = $current_page ?? '';
// Les URLs du site sont adaptées au dossier, puis échappées pour le HTML.
$headerUrl = static fn(string $path): string => htmlspecialchars(authUrl($path), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<header>
    <div>
        <div class="main_logo_container">
            <a href="<?= $headerUrl('index.php') ?>" class="logo"><img src="<?= $headerUrl('assets/images/icon-main.png') ?>" alt="Logo de Vault"></a>
            <h2><a href="<?= $headerUrl('index.php') ?>">VAULT</a></h2>
        </div>
        <nav aria-label="Navigation principale">
            <a class="<?= $current_page === 'index' ? 'selected' : '' ?>" href="<?= $headerUrl('index.php') ?>">Catalogue</a>
            <a class="<?= $current_page === 'profil' ? 'selected' : '' ?>" href="<?= $headerUrl($headerUser ? 'profile.php' : 'login.php') ?>">Mon Profil</a>
            <!-- Le lien Administration est réservé aux sessions de rôle admin. -->
            <?php if ($headerUser && $headerUser['role'] === 'admin'): ?>
                <a class="<?= $current_page === 'admin' ? 'selected' : '' ?>" href="<?= $headerUrl('admin.php') ?>">Administration
                    <span class="inline_admin_indicator">ADMIN</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="right_container">
        <div class="level_container">
            <div class="level_indicator" style="--percentage: <?= (int) ($percentage ?? 0) ?>%;"></div>
            <p class="level">Niv. 2</p>
            <p class="dot_separator">•</p>
            <p class="xp">135 XP</p>
        </div>
        <div class="search_bar">
            <img class="icon" src="<?= $headerUrl('assets/images/magnifying-glass.svg') ?>" alt="icon de loupe">
            <input type="text" placeholder="Rechercher film, série, jeu...">
            <p class="keyboard_indicator">CTRL+K</p>
        </div>
        <div class="auth-actions">
            <?php if ($headerUser): ?>
                <a class="account_menu" href="<?= $headerUrl('profile.php') ?>" aria-label="Ouvrir mon profil">
                    <img class="profile_picture" src="<?= $headerUrl('assets/images/profil-picture.jpg') ?>" alt="Photo de profil">
                    <span class="account-name"><?= htmlspecialchars($headerUser['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                </a>
                <!-- La déconnexion passe par POST et vérifie un jeton CSRF. -->
                <form class="logout-form" action="<?= $headerUrl('includes/actions/logout_action.php') ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <button type="submit" class="logout-button">Se déconnecter</button>
                </form>
            <?php else: ?>
                <a class="login-link" href="<?= $headerUrl('login.php') ?>">Se connecter</a>
                <a class="register-link" href="<?= $headerUrl('register.php') ?>">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</header>
