<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault - Inscription</title>
    <link rel="stylesheet" href="assets/css/pages/auth.css">
</head>
<body>
    <main class="auth-container">
        <section class="auth-card" aria-labelledby="register-title">
            <h1 id="register-title">Créer un compte</h1>

            <form action="includes/actions/register_action.php" method="post">
                <label for="nickname">Pseudo</label>
                <input type="text" id="nickname" name="nickname" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>

                <label for="password_confirmation">Confirmez le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>

                <button type="submit">S'inscrire</button>
            </form>

            <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
        </section>
    </main>
</body>
</html>