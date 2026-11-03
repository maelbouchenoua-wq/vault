<header>
    <div>
        <div class="main_logo_container">
            <a href="/index.php" class="logo"><img src="/assets/images/icon-main.png" alt="Logo de Vault"></a>
            <h2><a href="/index.php">VAULT</a></h2>
        </div>
        <nav>
            <a class="<?= $current_page == 'index' ? 'selected' : '' ?>" href="/index.php">Catalogue</a>
            <a class="<?= $current_page == 'profil' ? 'selected' : '' ?>" href="/profile.php">Mon Profil
                <span class="inline_level_indicator">NIV. 2</span>
            </a>
            <a class="<?= $current_page == 'admin' ? 'selected' : '' ?>" href="/admin.php">Administration
                <span class="inline_admin_indicator">ADMIN</span>
            </a>
            <a class="register-link" href="register.php">S'inscrire</a>
        </nav>
    </div>
    <div class="right_container">
        <div class="search_bar">
            <img class="icon" src="/assets/images/magnifying-glass.svg" alt="icon de loupe">
            <input type="text" placeholder="Rechercher film, série, jeu...">
            <p class="keyboard_indicator">CTRL+K</p>
        </div>
        <div class="level_container">
            <div class="level_indicator"></div>
            <p class="level">Niv. 2</p>
            <p class="dot_separator">•</p>
            <p class="xp">135 XP</p>
        </div>
        <div class="account_menu">
            <img class="profile_picture" src="/assets/images/profil-picture.jpg" alt="Photo de profil">
            <img class="caret" src="/assets/images/arrow-down.svg" alt="Fleche vers le bas">
        </div>
    </div>
</header>
