# Projet Vault

> Groupe: Mael, Victor, Urllanda, Gabriel

Ceci est un projet de site web, un réseau social centré sur la Pop-culture.

## Base de donnée

> MySQL - InnoDB version 8.0

<img width="2420" height="1475" alt="image" src="https://github.com/user-attachments/assets/0d2eccf9-3fd4-4041-b823-26297ce45ba4" />

## Mise en place

Pour mettre en place le projet sur votre machine :

1. Recréation de la base de donnée : Installez un serveur `MySQL` puis exécutez le script `sql/schema.sql`. Celui-ci va créer une nouvelle base de donnée `vault`.

2. Le site est généré via PHP, pour acceder au site : Placer simplement ce dossier dans celui de votre serveur PHP, puis accédez à `/index.php`.

## Tester le mini-jeu secret

Le mini-jeu **Intervention** se découvre depuis le logo du site.

- **Souris :** cliquer cinq fois sur l'image du logo VAULT en moins de trois secondes, du premier au cinquième clic. Le compteur repart à zéro si les trois secondes sont atteintes avant le cinquième clic.
- **Clavier :** cliquer hors d'un champ de saisie, maintenir **Ctrl**, puis taper successivement **F**, **E**, **U**. On peut aussi sélectionner le lien du logo avec **Tab** et l'activer cinq fois avec **Entrée** en moins de trois secondes.
- **Fermer :** appuyer sur **Échap**, puis cliquer sur le bouton **Quitter**. Le bouton **Échap · Menu** permet aussi d'ouvrir ce menu à la souris ou au toucher.

Pour vérifier le délai : quatre clics ne doivent rien ouvrir ; attendre au moins trois secondes, puis quatre nouveaux clics ne doivent toujours rien ouvrir. Après une nouvelle attente, cinq clics rapides doivent ouvrir le jeu.

Sans les cinq clics, le lien de l'image renvoie au catalogue à la fin des trois secondes. Le lien texte **VAULT**, à côté de l'image, garde son retour immédiat au catalogue. Après déblocage, le bouton **Rejouer à Intervention** permet de rouvrir le jeu.

Dans le jeu : **Flèches** ou **ZQSD** pour se déplacer, **E** pour agir, souris pour viser, clic gauche ou **Espace** pour arroser.
