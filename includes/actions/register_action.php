<?php
/** Traite l'inscription : vérifie le formulaire, hache le mot de passe et crée un compte user. */
// __DIR__ pointe ici sur includes/actions ; ../auth.php charge les outils communs de session et de CSRF.
require_once __DIR__ . '/../auth.php';
startSession();

// L'action reçoit un formulaire POST ; un accès direct renvoie vers la page d'inscription.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . authUrl('register.php'), true, 303);
    exit;
}

header('Cache-Control: no-store');

// Un utilisateur déjà connecté est envoyé vers son profil avant toute création de compte.
if (currentUser() !== null) {
    header('Location: ' . authUrl('profile.php'), true, 303);
    exit;
}

/** Lit un champ POST sous forme de chaîne ; une valeur absente ou envoyée comme tableau devient vide. */
function postedString(string $name): string
{
    $value = $_POST[$name] ?? '';

    return is_string($value) ? $value : '';
}

// Retirer les espaces autour du pseudo et de l'e-mail, mais conserver le mot de passe exactement saisi.
$nickname = trim(postedString('nickname'));
$email = trim(postedString('email'));
$password = postedString('password');
$passwordConfirmation = postedString('password_confirmation');
$errors = [];
// 422 signifie que les données sont reçues mais ne permettent pas de valider le formulaire.
$errorStatus = 422;

// Le jeton CSRF vérifie que le formulaire est lié à la session ouverte sur le site.
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $errors['csrf'] = 'Le formulaire a expiré. Veuillez réessayer.';
    $errorStatus = 403;
}

// Le pseudo est obligatoire et doit tenir dans les 50 caractères prévus par la base.
if ($nickname === '') {
    $errors['nickname'] = 'Veuillez saisir un pseudo.';
} elseif (!mb_check_encoding($nickname, 'UTF-8') || mb_strlen($nickname, 'UTF-8') > 50) {
    $errors['nickname'] = 'Le pseudo doit contenir au maximum 50 caractères valides.';
}

// Contrôler le format de l'e-mail et sa longueur côté serveur, même si le navigateur les vérifie aussi.
if ($email === '') {
    $errors['email'] = 'Veuillez saisir une adresse e-mail.';
} elseif (strlen($email) > 80 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors['email'] = 'Veuillez saisir une adresse e-mail valide de 80 caractères maximum.';
}

// mb_strlen compte les caractères ; strlen compte les octets, importants pour la limite de bcrypt.
// Un accent ou un emoji peut occuper plusieurs octets. Le caractère NUL est également refusé avant le hachage.
if (!mb_check_encoding($password, 'UTF-8') || mb_strlen($password, 'UTF-8') < 8) {
    $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères valides.';
} elseif (strlen($password) > 72) {
    $errors['password'] = 'Le mot de passe est trop long (72 octets maximum).';
} elseif (str_contains($password, "\0")) {
    $errors['password'] = 'Le mot de passe contient un caractère non autorisé.';
}

// La confirmation sert uniquement à détecter une erreur de saisie ; elle n'est pas enregistrée.
if ($passwordConfirmation === '') {
    $errors['password_confirmation'] = 'Veuillez confirmer le mot de passe.';
} elseif ($password !== $passwordConfirmation) {
    $errors['password_confirmation'] = 'Les deux mots de passe ne correspondent pas.';
}

// N'interroger et ne modifier la base que si toutes les validations précédentes ont réussi.
if ($errors === []) {
    require_once __DIR__ . '/../db.php';

    try {
        // true permet d'intercepter ici une indisponibilité de la connexion PDO.
        $pdo = getPDO(true);
        // email est un paramètre : execute() fournit la donnée séparément du texte SQL.
        $query = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $query->execute(['email' => $email]);

        // fetchColumn() renvoie l'identifiant trouvé, ou false si aucun compte n'utilise cette adresse.
        if ($query->fetchColumn() !== false) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée. Choisissez-en une autre.';
        } else {
            // Crée une empreinte bcrypt ; PHP génère le sel automatiquement.
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            // Le rôle est fixé à user dans le SQL : une valeur role=admin envoyée par le navigateur est ignorée.
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
        // Deux demandes simultanées peuvent passer le SELECT : la contrainte UNIQUE reste la protection finale.
        // Le code MySQL 1062 désigne un doublon ; les autres erreurs reçoivent un message générique.
        if ($exception->getCode() === '23000' && (int) ($exception->errorInfo[1] ?? 0) === 1062) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée. Choisissez-en une autre.';
        } else {
            $errorStatus = 503;
            $errors['database'] = 'L’inscription est momentanément indisponible. Veuillez réessayer plus tard.';
        }
    }
}

// En cas d'erreur, réafficher le formulaire avec le pseudo et l'e-mail, sans réinjecter les mots de passe.
if ($errors !== []) {
    http_response_code($errorStatus);
    $values = ['nickname' => $nickname, 'email' => $email];
    require __DIR__ . '/../../register.php';
    exit;
}

// Après réussite, la redirection 303 mène à une page GET et évite de renvoyer le formulaire lors d'un rafraîchissement.
header('Location: ' . authUrl('login.php?registered=1'), true, 303);
exit;
