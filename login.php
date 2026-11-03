<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault - Connexion</title>
    <link rel="stylesheet" href="assets/css/pages/auth.css">
</head>
<body>
    <main class="auth-container">
        <section class="auth-card" aria-labelledby="login-title">
            <h1 id="login-title">Connexion</h1>

            <form action="#" method="post">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Se connecter</button>
            </form>

            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        </section>
    </main>
</body>
</html>
