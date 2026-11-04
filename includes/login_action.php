<?php
require_once __DIR__ . '/auth.php';
startSession();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . authUrl('login.php'), true, 302);
    exit;
}

$loginEmail = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$loginError = 'Adresse e-mail ou mot de passe incorrect.';

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    $loginError = 'La session du formulaire a expiré. Veuillez réessayer.';
} elseif ($loginEmail === '' || strlen($loginEmail) > 80
    || filter_var($loginEmail, FILTER_VALIDATE_EMAIL) === false
    || $password === '' || !mb_check_encoding($password, 'UTF-8')
    || mb_strlen($password, 'UTF-8') > 128) {
    http_response_code(401);
} else {
    require_once __DIR__ . '/db.php';

    try {
        $pdo = getPDO(true);
        $query = $pdo->prepare('SELECT id, username, email, password, role FROM users WHERE email = :email LIMIT 1');
        $query->execute(['email' => $loginEmail]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        // Empreinte de secours si aucun compte ne correspond à l’email.
        $passwordHash = password_hash('compte-inexistant', PASSWORD_BCRYPT);
        if ($user !== false) {
            $passwordHash = $user['password'];
        }

        // Les anciens comptes Argon2 peuvent garder des mots de passe plus longs.
        $invalidBcryptInput = password_get_info($passwordHash)['algoName'] === 'bcrypt'
            && (strlen($password) > 72 || str_contains($password, "\0"));
        $passwordIsValid = !$invalidBcryptInput && password_verify($password, $passwordHash);

        if ($user !== false && $passwordIsValid) {
            if (!session_regenerate_id(true)) {
                throw new RuntimeException('Session renewal failed.');
            }

            $_SESSION = [
                'user' => [
                    'id' => (int) $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'] === 'admin' ? 'admin' : 'user',
                ],
                'csrf_token' => bin2hex(random_bytes(32)),
            ];

            header('Location: ' . authUrl('profile.php'), true, 303);
            exit;
        }

        http_response_code(401);
    } catch (PDOException | RuntimeException $exception) {
        http_response_code(503);
        $loginError = 'La connexion est momentanément indisponible. Veuillez réessayer plus tard.';
    }
}

require __DIR__ . '/../login.php';
