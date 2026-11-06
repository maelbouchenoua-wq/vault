<?php
// Accueil : catalogue public ou collection personnelle.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

startSession();
$user = currentUser();


// Vérifie si une note a été envoyée.
if (isset($_POST['rating'])) {

    // L'utilisateur doit être connecté.
    if ($user === null) {
        header('Location: ' . authUrl('login.php'));
        exit;
    }

    // Récupère les informations envoyées par le formulaire.
    $mediaId = filter_input(INPUT_POST, 'media_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

    // Vérifie que les deux valeurs sont correctes.
    if (
        $mediaId !== false && $mediaId !== null && $mediaId > 0 &&
        $rating !== false && $rating !== null && $rating >= 1 && $rating <= 5
    ) {
        $pdo = getPDO();

        // Vérifie que le média appartient à la collection de l'utilisateur.
        $query = $pdo->prepare(
            "SELECT id FROM user_media
             WHERE user_id = :user_id AND media_id = :media_id"
        );

        $query->execute([
            'user_id' => $user['id'],
            'media_id' => $mediaId
        ]);

        $existingMedia = $query->fetch();

        if ($existingMedia) {

            // Enregistre la nouvelle note.
            $query = $pdo->prepare(
                "UPDATE user_media
                 SET rating = :rating
                 WHERE user_id = :user_id AND media_id = :media_id"
            );

            $query->execute([
                'rating' => $rating,
                'user_id' => $user['id'],
                'media_id' => $mediaId
            ]);

        }
    }

    // Recharge le catalogue après le traitement.
    header('Location: index.php');
    exit;
}


// Vérifie si le formulaire Add a été envoyé.
if (isset($_POST['add_media'])) {

    // Vérifie que l'utilisateur est connecté.
    if ($user === null) {
        header('Location: ' . authUrl('login.php'));
        exit;
    }

    // Récupère l'identifiant du média envoyé par le formulaire.
    $mediaId = filter_input(INPUT_POST, 'media_id', FILTER_VALIDATE_INT);

    if ($mediaId !== false && $mediaId !== null && $mediaId > 0) {

        $pdo = getPDO();

        // Vérifie si le média est déjà dans la collection.
        $query = $pdo->prepare(
            "SELECT id FROM user_media
             WHERE user_id = :user_id AND media_id = :media_id"
        );

        $query->execute([
            'user_id' => $user['id'],
            'media_id' => $mediaId
        ]);

        $existingMedia = $query->fetch();

        // Ajoute le média uniquement s'il n'existe pas déjà.
       if (!$existingMedia) {
    $query = $pdo->prepare(
        "INSERT INTO user_media (user_id, media_id, status, rating)
         VALUES (:user_id, :media_id, 'planned', NULL)"
    );

    $query->execute([
        'user_id' => $user['id'],
        'media_id' => $mediaId
    ]);

    $_SESSION['message'] = "success";
} else {
    $_SESSION['message'] = "exists";
}
    }

    // Recharge la page après le traitement.
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Vault - Accueil</title>
    <link rel="stylesheet" href="./assets/css/reset.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/components/header.css">
    <link rel="stylesheet" href="./assets/css/components/footer.css">
    <link rel="stylesheet" href="./assets/css/pages/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Space+Grotesk:wght@300..700&display=swap"
          rel="stylesheet">
</head>
<body>
    <?php
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>
<?php
$current_page = "index";
include __DIR__ . '/includes/templates/layout/header.php';
?>
<main>
    <!-- Présentation du site et indicateurs de démonstration. -->
    <div class="hero-banner">
        <div class="sprint-bdd-info">
            <div class="number-index-div">
                <div class="sprint-number">
                    ARCHIVE_NATIVE // SPRINT_01
                </div>
                <div class="index">
                    INDEX CENTRALISE MULTIMEDIA
                </div>
            </div>

            <div class="bdd-xp-div">
                <div class="bdd-icon">
                    <img src="./assets/images/bdd-icon.svg" alt="icon">
                </div>
                <div class="bdd-info">
                    INDEX : 9 OEUVRE // SQLITE LOCAL
                </div>
                <div class="light-icon">
                    <img src="./assets/images/light_icon.svg" alt="icon">
                </div>
                <div class="xp-active">
                    XP SYNC ACTIVE
                </div>
            </div>
        </div>
        <h1> Explorer votre <span class="h1-color"> univers pop-culture </span></h1>

        <div class="banner-txt">
            <p>Conservez vos films favoris, suivez vos séries et bâtissez votre collection de jeux vidéo <br/>
                au sein d'un sanctuaire numérique haute fidélité.</p>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/templates/pages/index/catalogue/catalogue.php';
    // null affiche le catalogue public ; sinon, la collection du compte.
    getCatalogue($user === null ? null : (int) $user['id']);
    ?>
    <section>
        <div class="banner">

            <div>
                <p class="banner_texte">
                    <img class="banner_icon" width="14.67px" height="14px" src="./assets/icon/Icon.svg" alt="Logo de Vault">
                    ARCHITECTURE WEB SPRINT 1 & 4

                </p>
                <p class="banner_texte1"> Progression de votre Archivage Pop-Culture

                </p>
                <p class="banner_texte2">
                <p>Chaque œuvre annotée enrichit votre rang et consolide la persistance locale de vos
                    trésors culturels.
                </p>

                <div class="banner_text6">
                    <div>
                        <p class="banner_text3">6/9

                        </p>
                        <P class="banner_text4">
                            ŒUVRES AMORCÉES
                        </P>
                    </div>
                    <div>
                        <p class="banner_text5">4.8 / 5</p>
                    </div>

                </div>

            </div>
            <div>

            </div>
        </div>
    </section>
</main>
<?php  include __DIR__ . '/includes/templates/layout/footer.php'; ?>

<?php if ($message !== null): ?>
    <div class="modal-overlay" id="successModal">
        <div class="modal-box">
            <div class="modal-icon">
                <span>✓</span>
            </div>

            <h2>
                <?= $message === 'success'
                    ? 'Ajout réussi !'
                    : 'Déjà dans votre collection !' ?>
            </h2>

            <p>
                <?= $message === 'success'
                    ? 'Le média a bien été ajouté à votre collection.'
                    : 'Ce média existe déjà dans votre collection.' ?>
            </p>

            <button type="button" id="closeModal">
                Continuer
            </button>
        </div>
    </div>
<?php endif; ?>
<script>
    const closeButton = document.getElementById('closeModal');
    const modal = document.getElementById('successModal');

    if (closeButton && modal) {
        closeButton.addEventListener('click', function () {
            modal.remove();
        });
    }
</script>
</body>
</html>
