# Tests

Les tests sont regroupés par sujet. Ils ne sont pas nécessaires au fonctionnement
du site en production.

| Dossier | Contenu |
| --- | --- |
| `Framework/` | Validation, configuration, conteneur, sessions, cache et messages flash |
| `Domain/` | XP, collections, profil et listes manga |
| `Assets/` | CSS, JavaScript, versions des fichiers et images de profil |
| `Build/` | Écriture atomique, verrous, archives et dépendances de production |
| `Http/` | Routes HTTP, navigation SPA, cache HTTP et rapports |
| `Browser/` | Scénarios JavaScript et lanceurs Microsoft Edge |
| `Docs/` | Guide détaillé et audit de performances |

## Commandes

Depuis la racine du projet :

```sh
composer check             # Analyse PHP + régressions + HTTP
composer regression-tests  # Régressions uniquement
composer http-tests        # Routes HTTP et SPA
composer browser-tests     # Scénarios navigateur
composer check:all         # Toutes les suites précédentes
```

Les tests HTTP nécessitent Apache local et les identifiants `HTTP_TEST_USERNAME`
et `HTTP_TEST_PASSWORD` dans `.env`. Certains tests métier nécessitent MySQL ;
les scénarios navigateur nécessitent Microsoft Edge.

Pour lancer un test précis :

```sh
php tests/Framework/framework.php
php tests/Domain/achievement-xp.php
php tests/Browser/run-page-styles-browser.php http://localhost/lolissr tests/Browser/spa-browser.js
```

Les commandes Composer conservent leur sélection de tests. Les vérifications
complémentaires et leurs prérequis sont décrits dans le [guide](Docs/guide.md).
Voir aussi les [tests des styles](Docs/page-styles.md) et
l'[audit de performances](Docs/performance-audit.md).

Les rapports HTTP générés restent dans `Http/reports/` et sont ignorés par Git.
