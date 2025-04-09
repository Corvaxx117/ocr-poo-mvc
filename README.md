# Mini Framework PHP MVC

Ce projet est une application PHP développée dans le cadre de la formation OpenClassRoom. Il s'agit d'un mini-framework MVC (Model-View-Controller) inspiré de Symfony, conçu pour mieux comprendre la programmation orientée objet (POO) et les principes d'architecture MVC, ainsi que Symfony sans l'utiliser directement.

# Projet – Amélioration du blog d'Émilie Forteroche

Ce projet ajoute de nouvelles fonctionnalités côté administration comme gérer les articles sans passer par la base de données mais directement depuis le blog, permet également à un utilisateur de se connecter, de visualiser, modifier ou supprimer son compte. tout en respectant une exigence : **ne pas utiliser de librairie tierce**.

---

## Contexte de la mission

L'autrice Émilie Forteroche souhaite enrichir son blog avec une page d'administration plus complète. Cette nouvelle interface doit lui permettre de :

- Visualiser tous ses articles dans un tableau.
- Accéder à des **statistiques** (vues, commentaires, date de publication).
- **Trier dynamiquement** par colonne (titre, date, vues, commentaires).
- Supprimer facilement des **commentaires inappropriés** sans passer par la base de données.

---

## Fonctionnalités principales

### Côté technique

- Mise en place d’un **tableau de bord admin** avec :
  - Tri dynamique (ASC / DESC) sur 4 critères.
  - Affichage clair, ligne sur deux avec couleur alternée.
- **Suppression de commentaires** uniquement accessible par un admin.
- **Affichage des messages flashs** (succès, erreur, warning) sans session, cookie ou JSON.
- Redirections avec retour visuel (URL + message flash).
- Séparation des responsabilités strictement respectée (MVC).
- Gestion du design avec des **styles maison** (aucun framework CSS).
- **Effets visuels personnalisés** en JS (ex: hover dynamique inspiré d’Airbnb).

### Design & UX

- Design 100% personnalisé, cohérent avec le reste du site.
- Responsive fixe : interface adaptée à un écran de 1366 px de large.
- Interaction fluide et feedback utilisateur immédiat.

---

## Structure du projet

| Élément      | Dossier           | Description                                       |
| ------------ | ----------------- | ------------------------------------------------- |
| Modèle       | `src/Model/`      | Requêtes SQL, accès à la base de données.         |
| Vue          | `views/`          | Affichage HTML enrichi avec PHP.                  |
| Contrôleur   | `src/Controller/` | Gère les interactions utilisateur.                |
| Services     | `src/Services/`   | Utilitaires réutilisables (flash, view, auth...). |
| Cœur système | `src/Core/`       | Fichiers fondamentaux créés à la main.            |

---

## Fichiers cœur du mini-framework

Ces fichiers ont été entièrement conçus pour faire fonctionner l'application sans dépendance :

- **`index.php`** : point d’entrée de l’application, initialise tout.
- **`Launcher.php`** : exécute les bons contrôleurs/méthodes après parsing.
- **`Router.php`** : gère les routes dynamiques depuis les URLs.
- **`Database.php`** : classe singleton pour une connexion PDO unique.
- **`ErrorHandler.php`** : attrape et affiche les erreurs personnalisées.

Cette logique artisanale permet une maîtrise complète du fonctionnement du projet, depuis l’entrée de l’utilisateur jusqu’à l’affichage final.

---

## Exemples concrets de fonctionnalités ajoutées

- `AdminController::showAdmin()` : affiche le tableau des articles avec tri.
- `CommentController::delete()` : suppression sécurisée d’un commentaire.
- `FlashMessage` : affichage des alertes flash avec styles adaptés.
- `HoverEffect` (JS) : effet de lumière dynamique à la souris sur les boutons ou en-têtes.
- `redirectWithFlash()` : méthode de redirection intelligente avec feedback utilisateur.

---

## Installation & configuration

### Prérequis

- PHP 7.4+ (PHP 8 recommandé)
- Serveur Apache (avec mod_rewrite)
- Base de donées MySQL
- Composer (gestionnaire de dépendances PHP)

### Étapes

1. Cloner le dépôt initial du blog :

   ```bash
   git clone https://github.com/OpenClassrooms-Student-Center/PHP-blog-emilie-forteroche.git
   cd blog-emilie-forteroche

   ```

2. Cloner le dépôt avancé du blog :
   ```bash
   git clone https://github.com/Corvaxx117/ocr-poo-mvc.git
   cd ocr-poo-mvc
   ```

### Configuration d'un fichier .env

- DB_HOST=localhost
- DB_NAME=nom_de_la_base
- DB_USER=root
- DB_PASSWORD=motdepasse

Dans le fichier config.php, définir l'url de base de votre projet pour pouvoir être utilisé au sein de l'application

- define('APP_BASE_URL', $\_ENV['APP_BASE_URL'] ?? $\_SERVER['APP_BASE_URL'] ?? 'http://chemin-vers-mon-site/public');
