<?php

/* Charge les paramètres depuis le dossier de ce fichier. */
require_once __DIR__ . '/config.php';

/* Réutilise une connection PDO pendant la requête courante. */
function getPDO(bool $throwOnConnectionError = false): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        /* Transmet l’erreur ou affiche un message générique au visiteur. */
        } catch (PDOException $e) {
            if ($throwOnConnectionError) {
                throw $e;
            }
            error_log("PDO Error: " . $e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }

    return $pdo;
}
