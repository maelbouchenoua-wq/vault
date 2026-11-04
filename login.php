<?php
require_once __DIR__ . '/includes/auth.php';
startSession();
$loginError = $loginError ?? '';
$loginEmail = $loginEmail ?? '';

if ($loginError === '' && currentUser() !== null) {
    header('Location: ' . authUrl('profile.php'), true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault - Connexion</title>
    <link rel="stylesheet" href="<?= authUrl('assets/css/pages/auth.css') ?>">
</head>
<body>
    <main class="auth-container">
        <section class="auth-card" aria-labelledby="login-title">
            <h1 id="login-title">Connexion</h1>

            <?php if ($loginError !== ''): ?>
                <p role="alert"><?= htmlspecialchars($loginError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php elseif (($_GET['registered'] ?? null) === '1'): ?>
                <p role="status">Votre compte a été créé. Vous pouvez vous connecter.</p>
            <?php elseif (($_GET['logged_out'] ?? null) === '1'): ?>
                <p role="status">Vous êtes déconnecté.</p>
            <?php endif; ?>

            <form action="<?= authUrl('includes/actions/login_action.php') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" maxlength="80" autocomplete="email"
                       value="<?= htmlspecialchars($loginEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" maxlength="128" autocomplete="current-password" required>

                <button type="submit">Se connecter</button>
            </form>

            <p>Pas encore de compte ? <a href="<?= authUrl('register.php') ?>">S'inscrire</a></p>
            <p><a href="<?= authUrl('index.php') ?>">Consulter le catalogue</a></p>
        </section>
    </main>
</body>
</html>