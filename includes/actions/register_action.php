<?php

// Vérifie le formualire avant de créer le compte.
require_once __DIR__ . '/../auth.php';
startSession();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . authUrl('register.php'), true, 303);
    exit;
}

header('Cache-Control: no-store');

if (currentUser() !== null) {
    header('Location: ' . authUrl('profile.php'), true, 303);
    exit;
}

// Lit un champ POST en refusant les tableau.
function postedString(string $name): string
{
    $value = $_POST[$name] ?? '';

    return is_string($value) ? $value : '';
}

$nickname = trim(postedString('nickname'));
$email = trim(postedString('email'));
$password = postedString('password');
$passwordConfirmation = postedString('password_confirmation');
$errors = [];

$errorStatus = 422;

// Vérifie que le formulaire appartient à cette session.
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $errors['csrf'] = 'Le formulaire a expiré. Veuillez réessayer.';
    $errorStatus = 403;
}

// Contrôle le pseudo et le format de l’e-mail.
if ($nickname === '') {
    $errors['nickname'] = 'Veuillez saisir un pseudo.';
} elseif (!mb_check_encoding($nickname, 'UTF-8') || mb_strlen($nickname, 'UTF-8') > 50) {
    $errors['nickname'] = 'Le pseudo doit contenir au maximum 50 caractères valides.';
}

if ($email === '') {
    $errors['email'] = 'Veuillez saisir une adresse e-mail.';
} elseif (strlen($email) > 80 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors['email'] = 'Veuillez saisir une adresse e-mail valide de 80 caractères maximum.';
}

// Vérifie longueur, limite bcrypt et confirmation du mot de passe.
if (!mb_check_encoding($password, 'UTF-8') || mb_strlen($password, 'UTF-8') < 8) {
    $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères valides.';
} elseif (strlen($password) > 72) {
    $errors['password'] = 'Le mot de passe est trop long (72 octets maximum).';
} elseif (str_contains($password, "\0")) {
    $errors['password'] = 'Le mot de passe contient un caractère non autorisé.';
}

if ($passwordConfirmation === '') {
    $errors['password_confirmation'] = 'Veuillez confirmer le mot de passe.';
} elseif ($password !== $passwordConfirmation) {
    $errors['password_confirmation'] = 'Les deux mots de passe ne correspondent pas.';
}

if ($errors === []) {
    require_once __DIR__ . '/../db.php';

    try {
        $pdo = getPDO(true);
        // Recherche l’e-mail avec une requête SQL préparée.
        $query = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $query->execute(['email' => $email]);

        if ($query->fetchColumn() !== false) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée. Choisissez-en une autre.';
        } else {
            // Hache le mot de passe et impose le rôle user.
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare(
                "INSERT INTO users (username, email, password, role)
                 VALUES (:username, :email, :password, 'user')"
            );
            $insert->execute([
                'username' => $nickname,
                'email' => $email,
                'password' => $passwordHash,
            ]);
        }
    } catch (PDOException $exception) {

        if ($exception->getCode() === '23000' && (int) ($exception->errorInfo[1] ?? 0) === 1062) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée. Choisissez-en une autre.';
        } else {
            $errorStatus = 503;
            $errors['database'] = 'L’inscription est momentanément indisponible. Veuillez réessayer plus tard.';
        }
    }
}

// Réaffiche les erreurs sans conserver les mots de passe.
if ($errors !== []) {
    http_response_code($errorStatus);
    $values = ['nickname' => $nickname, 'email' => $email];
    require __DIR__ . '/../../register.php';
    exit;
}

header('Location: ' . authUrl('login.php?registered=1'), true, 303);
exit;
