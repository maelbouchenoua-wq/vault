<?php
// En-tête commun : liens adapté à la session et à la page active.
require_once __DIR__ . '/../../libs/auth.php';
require_once __DIR__ . '/../../controllers/profil_controller.php';
startSession();
$headerUser = currentUser();
$current_page = $current_page ?? '';
// Les URLs du site sont adaptées au dossier, puis échappées pour le HTML.
$headerUrl = static fn(string $path): string => htmlspecialchars(authUrl($path), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$profileUser = currentUser();

$result1 = get_current_user_exp();
# permet de récuperer les xp de niveau actuel
$currentLevelExp1 = $result1['total_xp'] % 100;
# permet de récuperer les xp
$currentExpTotal = $result1['total_xp'];
# permet de récuperer le level
$currentLevel1 = $result1['level'];

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
            <div class="level_indicator" style="--percentage: <?= $currentLevelExp1 ?>%;"></div>
            <p class="level">Niv. <?= $currentLevel1 ?></p>
            <p class="dot_separator">•</p>
            <p class="xp"><?= $currentExpTotal ?> XP</p>
        </div>
        <div class="search_bar">
            <img class="icon" src="<?= $headerUrl('assets/images/magnifying-glass.svg') ?>" alt="icon de loupe">
            <input class ="search-bar1" type="text" placeholder="Rechercher film, série, jeu...">
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
    <script src="../../../assets/js/filter_header.js" defer></script>
</header>
