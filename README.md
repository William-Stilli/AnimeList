# Ultimate Anime Tracker 📺

## ⚠️ Avertissement / Disclaimer

**Pour les francophones :**
Cette application a été développée sur mesure, strictement pour mon utilisation personnelle. Étant un projet privé ouvert au public, il est fort probable que quelques bugs se cachent encore dans les recoins du code. De plus, l'interface et la logique sont pensées pour mon propre confort, et il n'est absolument pas prévu d'ajouter une traduction en anglais ou de l'adapter pour un usage général pour le moment. Si vous décidez de l'utiliser ou de la forker, c'est à vos risques et périls !

**For English speakers:**
This application was built strictly for my own personal use. As it is a personal project made public, expect to encounter a few bugs and unexpected behaviors here and there. Furthermore, the application is tailored to my specific needs, and there are currently no plans to provide an English translation or global localization. Feel free to explore the code, but use or fork it at your own risk!

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Inertia](https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white)

> Une application web moderne pour suivre ta progression d'animés et gérer ta bibliothèque. Plus qu'une simple liste, c'est une expérience gamifiée pour les vrais passionnés.

## Fonctionnalités Principales

Ce projet n'est pas un simple clone de MyAnimeList. C'est une version avec des fonctionnalités sur mesure :

-   **Recherche Intelligente :** Intégration complète de l'API **Jikan (MyAnimeList)** pour trouver n'importe quel animé.
-   **Gestion de Couvertures Personnalisée :**
    -   L'image par défaut ne te plaît pas ? Change-la !
    -   Choisis parmi la **galerie officielle**.
    -   Système robuste qui garde tes préférences même après un refresh.
-   **Gamification Avancée :**
    -   Gagne de l'**XP** à chaque épisode regardé.
    -   Monte de niveau.
    -   Débloque des **Badges** (Romance Lover, No-Life...).
-   **Easter Eggs Cachés :**
    -   Le système analyse ce que tu regardes. Certaines conditions débloquent des badges secrets et uniques.
-   **Dashboard Statistiques :** Suis ton temps de visionnage total (jours/heures) et ta répartition par genres.

## Stack Technique

-   **Backend :** Laravel 11 / 12
-   **Frontend :** Vue 3 (Composition API) + Inertia.js
-   **Base de données :** SQLite
-   **Styling :** Tailwind CSS + Shadcn/UI components
-   **Qualité Code :** ESLint, Prettier, TypeScript

## Installation & Démarrage

Tu veux lancer le projet chez toi ? Suis le guide :

## 🐳 Installation rapide avec Docker

Pour garantir une installation fluide et éviter les conflits d'environnement, ce projet est entièrement conteneurisé. Assurez-vous d'avoir [Docker](https://www.docker.com/) installé sur votre machine avant de commencer.

Exécutez les commandes suivantes dans votre terminal :

**1. Récupérer le code source et entrer dans le répertoire :**
    ```bash
    git clone [https://github.com/William-Stilli/AnimeList.git](https://github.com/William-Stilli/AnimeList.git)
    cd AnimeList
    cp .env.example .env
    docker compose up -d --build
    docker compose exec app php artisan key:generate
    docker compose exec app php artisan migrate:fresh --seed
    docker compose exec app chown -R www-data:www-data /var/www/database
    ```

    Déploiement terminé ! L'application est désormais accessible depuis votre navigateur à l'adresse suivante : http://localhost:8000

## Commandes Utiles

-   **Recalculer tous les badges :** Si tu as modifié les règles ou ajouté des animés manuellement en BDD.
    ```bash
    php artisan badges:reset-all
    ```
-   **Reset complet (Attention, efface tout !) :**
    ```bash
    php artisan migrate:fresh --seed
    ```

## Auteur

**William Stilli**

---

## 📄 Licence

Ce projet est sous licence [MIT](https://opensource.org/licenses/MIT). Fais-en ce que tu veux, amuse-toi !
