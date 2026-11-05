<?php
// Formulaire d'inscription et réaffichage des erreurs du serveur.
require_once __DIR__ . '/includes/auth.php';
startSession();

// Un membre déjà connecté est rediriger vers son profil.
if (currentUser() !== null) {
    header('Location: ' . authUrl('profile.php'), true, 303);
    exit;
}

$errors = $errors ?? [];
$values = $values ?? ['nickname' => '', 'email' => ''];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault - Inscription</title>
    <link rel="stylesheet" href="<?= authUrl('assets/css/pages/auth.css') ?>">
</head>
<body>
    <main class="auth-container">
        <section class="auth-card" aria-labelledby="register-title">
            <h1 id="register-title">Créer un compte</h1>

            <!-- Messages et saisies conservées sont échappés dans le HTML. -->
            <?php if ($errors !== []): ?>
                <div role="alert" id="register-errors">
                    <p>Veuillez corriger les erreurs suivantes :</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- POST et jeton CSRF ; les valeurs sont vérifiées côté serveur. -->
            <form action="<?= authUrl('includes/actions/register_action.php') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <label for="nickname">Pseudo</label>
                <input type="text" id="nickname" name="nickname" maxlength="50" autocomplete="username"
                       value="<?= htmlspecialchars($values['nickname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" maxlength="80" autocomplete="email"
                       value="<?= htmlspecialchars($values['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" minlength="8" maxlength="72"
                       autocomplete="new-password" aria-describedby="password-help" required>
                <small id="password-help">8 caractères minimum. 72 octets maximum : certains caractères, comme les accents ou les émojis, comptent pour plusieurs octets.</small>

                <label for="password_confirmation">Confirmez le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       minlength="8" maxlength="72" autocomplete="new-password" required>

                <button type="submit">S'inscrire</button>
            </form>

            <p>Déjà inscrit ? <a href="<?= authUrl('login.php') ?>">Se connecter</a></p>
            <p><a href="<?= authUrl('index.php') ?>">Consulter le catalogue</a></p>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/templates/layout/fire-game.php'; ?>
</body>
</html>
