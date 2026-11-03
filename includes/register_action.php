<?php
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ../register.php');
    exit;
}

header('Cache-Control: no-store');

function formValue(string $name): string
{
    $value = $_POST[$name] ?? '';

    return is_string($value)
        ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault - Données d'inscription</title>
    <link rel="stylesheet" href="../assets/css/pages/auth.css">
</head>
<body>
    <main class="auth-container">
        <section class="auth-card" aria-labelledby="register-result-title">
            <h1 id="register-result-title">Données du formulaire</h1>

            <p><strong>Pseudo :</strong> <?= formValue('nickname') ?></p>
            <p><strong>E-mail :</strong> <?= formValue('email') ?></p>
            <p><strong>Mot de passe :</strong> <?= formValue('password') ?></p>
            <p><strong>Confirmez le mot de passe :</strong> <?= formValue('password_confirmation') ?></p>

            <p><a href="../register.php">Retour au formulaire</a></p>
        </section>
    </main>
</body>
</html>
