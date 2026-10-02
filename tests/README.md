# TESTS

Les tests sont regroupés par sujet. Ils ne sont pas nécessaires au fonctionnement
du site en production.




```text
=================================================
ORGANISATION
=================================================
```


= Choisir la bonne suite

| Dossier | Contenu |
| --- | --- |
| `Framework/` | Validation, configuration, conteneur, sessions, cache et messages flash |
| `Domain/` | XP, collections, profil et listes manga |
| `Assets/` | CSS, JavaScript, versions des fichiers et images de profil |
| `Build/` | Écriture atomique, verrous, archives et dépendances de production |
| `Http/` | Routes HTTP, navigation SPA, cache HTTP et rapports |
| `Browser/` | Scénarios JavaScript et lanceurs Microsoft Edge |
| `Support/` | Bootstrap commun aux régressions et à PHPStan |

Les guides et audits sont centralisés dans [docs/](../docs/project-structure.md).
`Build/project-structure.php` vérifie les namespaces PSR-4, les imports JavaScript
et les liens locaux de la documentation.




```text
=================================================
COMMANDES
=================================================
```


= Ce que lancent les contrôles

```text
composer check:all
    │
    ├── composer check
    │       ├── PHPStan
    │       ├── Tests HTTP + SPA
    │       └── Régressions
    │
    └── composer browser-tests
            └── Scénarios dans Microsoft Edge
```


= Scripts de lancement
Depuis la racine du projet :

```sh
composer check             # Analyse PHP + régressions + HTTP
composer regression-tests  # Régressions uniquement
composer http-tests        # Routes HTTP et SPA
composer browser-tests     # Scénarios navigateur
composer check:all         # Toutes les suites précédentes
```




```text
=================================================
PRÉREQUIS
=================================================
```


= Services nécessaires

Les tests HTTP nécessitent Apache local et les identifiants `HTTP_TEST_USERNAME`
et `HTTP_TEST_PASSWORD` dans `.env`. Certains tests métier nécessitent MySQL ;
les scénarios navigateur nécessitent Microsoft Edge.




```text
=================================================
TEST CIBLÉ
=================================================
```


= Lancer un seul scénario

```sh
php tests/Framework/core-behavior.php
php tests/Domain/achievement-xp.php
php tests/Browser/run-browser-scenario.php http://localhost/lolissr tests/Browser/spa-browser.js
```




```text
=================================================
DOCUMENTATION ET RAPPORTS
=================================================
```


Les commandes Composer conservent leur sélection de tests. Les vérifications
complémentaires et leurs prérequis sont décrits dans le [guide](../docs/guide.md).
Voir aussi les [tests des styles](../docs/page-styles.md) et
l'[audit de performances](../docs/performance-audit.md).

Les rapports HTTP générés restent dans `Http/reports/` et sont ignorés par Git.
