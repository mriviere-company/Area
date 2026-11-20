# Area Universe — version originale (2017–2024)

*[English version below](#english)*

Jeu de stratégie spatiale multijoueur en temps réel, jouable dans le navigateur, **conçu, développé et exploité seul** à partir de 2017, ma première année de développeur web. Le jeu a atteint **un pic de 850 joueurs actifs**.

> **100 % écrit à la main, sans IA.** Tout le code de ce dépôt a été écrit sans assistant de code IA, entre 2017 et 2024 : conception du jeu, backend, frontend, base de données et mise en production, par une seule personne.

Ce dépôt est **archivé** : c'est la première version du jeu, conservée telle quelle. Le jeu a été entièrement refondu en 2024 sous le nom **Area Reborn** (dépôt privé, en production sur [areauniverse.com](https://www.areauniverse.com/)), cette fois avec des outils d'IA.

## En bref

| | |
|---|---|
| **Période** | 2017 → 2024 ; 947 commits sur GitHub d'avril 2018 à septembre 2024 |
| **Équipe** | 1 personne : game design, backend, frontend, UX/UI, base de données, déploiement |
| **Audience** | pic de 850 joueurs actifs |
| **Taille** | ~44 000 lignes de PHP, ~12 000 lignes de Twig, 35 entités Doctrine, 62 contrôleurs, 256 routes, 81 templates |
| **Langues du jeu** | français, anglais, allemand |
| **Stack** | PHP, Symfony, Doctrine ORM, MySQL, Twig, SCSS, Bootstrap, JavaScript natif et jQuery, Webpack Encore, tâches cron |
| **Évolution** | Symfony 4.0 (2018) → 5 (2020) → 6 (2022), PHP 7.1 → 8, sans réécriture, au fil des montées de version |

## Ce que contient le jeu

- **Un univers généré** : univers, galaxies, secteurs et planètes, avec génération des planètes et cartes navigables à trois niveaux.
- **Une économie** : bâtiments à construire ou détruire, production de ressources, coûts d'entretien quotidiens, marché d'échange entre joueurs.
- **Cinq arbres de recherche** : armement, militaire, exploration, sociologie et politique.
- **Des flottes** : vaisseaux personnalisables, déplacements, déploiement, combats spatiaux, invasions terrestres avec des soldats, champs d'astéroïdes.
- **La diplomatie** : alliances avec grades, pactes, guerres, paix et pactes de non-agression.
- **Héros, objets et prison**, plus un mode PvE contre des zombies.
- **La vie sociale** : salons de discussion, messagerie privée, rapports de combat, classements, statistiques, profils, quêtes et missions.
- **Un moteur de jeu planifié** : des tâches cron font avancer le monde (combats, mouvements de flottes, production, zombies, astéroïdes, fin de partie) ; des bots scriptés peuplent les serveurs.
- **Plusieurs serveurs de jeu** avec un cycle de vie complet jusqu'à la fin de partie, et un panneau d'administration.

## Organisation du code

| Dossier | Contenu |
|---|---|
| `src/Controller/Connected/` | les pages du jeu : planètes, flottes, recherche, alliances, carte, marché, messagerie… |
| `src/Controller/Connected/Execute/` | la logique exécutée à chaque tour : combats, flottes, production, zombies, fin de partie |
| `src/Controller/CronController/` | les points d'entrée des tâches planifiées et la création des bots |
| `src/Entity/` | le modèle de données (35 entités : `Planet`, `Fleet`, `Alliance`, `Commander`, `Report`…) |
| `src/Service/` | les services métier extraits au fil des refactorisations |
| `templates/` | les vues Twig |
| `translations/` | les traductions français, anglais et allemand |

## Ce que ce projet m'a appris

Avec le recul, ce code a les défauts d'un premier gros projet : beaucoup de logique dans les contrôleurs, peu de services, jQuery partout et aucun test. En 2021, j'ai mesuré les limites de cette structure. Ces leçons ont guidé la refonte **Area Reborn** :

- des entités mieux découpées pour éviter les conflits et les entités fantômes ;
- la logique dans des services et les données dans des repositories, des contrôleurs fins ;
- la suppression de jQuery au profit d'une application Vue 3 et TypeScript ;
- des tests automatisés sur les routes, les services et les formulaires critiques ;
- un code à jour avec les dernières versions de PHP et de Symfony ;
- une API pour encadrer les échanges avec l'extérieur, une interface pensée pour le mobile et un meilleur référencement ;
- un algorithme de combat et des tâches planifiées refactorisés et optimisés.

---

<a id="english"></a>

# Area Universe — original version (2017–2024)

A real-time multiplayer space strategy game played in the browser, **designed, built, and run single-handedly** starting in 2017, my first year as a web developer. The game peaked at **850 active players**.

> **100% hand-written, no AI.** All the code in this repository was written without any AI coding assistant, between 2017 and 2024: game design, backend, frontend, database, and deployment, by one person.

This repository is **archived**: it is the game's first version, kept as is. The game was fully rebuilt in 2024 as **Area Reborn** (private repository, live at [areauniverse.com](https://www.areauniverse.com/)), this time with AI tools.

## At a glance

| | |
|---|---|
| **Period** | 2017 → 2024; 947 commits on GitHub from April 2018 to September 2024 |
| **Team** | 1 person: game design, backend, frontend, UX/UI, database, deployment |
| **Audience** | peak of 850 active players |
| **Size** | ~44,000 lines of PHP, ~12,000 lines of Twig, 35 Doctrine entities, 62 controllers, 256 routes, 81 templates |
| **Game languages** | French, English, German |
| **Stack** | PHP, Symfony, Doctrine ORM, MySQL, Twig, SCSS, Bootstrap, native JavaScript and jQuery, Webpack Encore, cron jobs |
| **Evolution** | Symfony 4.0 (2018) → 5 (2020) → 6 (2022), PHP 7.1 → 8, upgraded in place without a rewrite |

## What the game includes

- **A generated universe**: universe, galaxies, sectors, and planets, with planet generation and three levels of navigable maps.
- **An economy**: buildings to construct or demolish, resource production, daily upkeep costs, and a player-to-player market.
- **Five research trees**: weaponry, military, exploration, sociology, and politics.
- **Fleets**: customizable ships, movement, deployment, space combat, ground invasions with soldiers, and asteroid fields.
- **Diplomacy**: alliances with ranks, pacts, wars, peace treaties, and non-aggression pacts.
- **Heroes, items, and a jail**, plus a PvE mode against zombies.
- **Social features**: chat rooms, private messages, battle reports, rankings, statistics, profiles, quests, and missions.
- **A scheduled game engine**: cron jobs advance the world (combat, fleet movement, production, zombies, asteroids, end of game); scripted bots populate the servers.
- **Multiple game servers** with a full lifecycle up to the end of a game, and an admin panel.

## Code layout

| Folder | Contents |
|---|---|
| `src/Controller/Connected/` | the game pages: planets, fleets, research, alliances, map, market, messages… |
| `src/Controller/Connected/Execute/` | the logic run each turn: combat, fleets, production, zombies, end of game |
| `src/Controller/CronController/` | scheduled-task entry points and bot creation |
| `src/Entity/` | the data model (35 entities: `Planet`, `Fleet`, `Alliance`, `Commander`, `Report`…) |
| `src/Service/` | business services extracted over successive refactorings |
| `templates/` | Twig views |
| `translations/` | French, English, and German translations |

## What this project taught me

In hindsight, this code has the flaws of a first large project: a lot of logic in controllers, few services, jQuery everywhere, and no tests. By 2021, I had reached the limits of that structure. Those lessons shaped the **Area Reborn** rebuild:

- better-scoped entities to avoid conflicts and ghost entities;
- logic in services and data access in repositories, with thin controllers;
- jQuery replaced by a Vue 3 and TypeScript application;
- automated tests on critical routes, services, and forms;
- code kept current with the latest PHP and Symfony releases;
- an API to control external interactions, a mobile-first interface, and better SEO;
- a refactored, optimized combat algorithm and scheduled jobs.
