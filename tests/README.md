# Tests

Les suites sont regroupées par type de contrôle, puis par fonctionnalité. Les noms des scénarios sont conservés.

| Dossier | Sous-dossiers |
| --- | --- |
| `Domain` | Manga, Collections, Chinois, Profile, Auth, Database, Shared |
| `Framework` | Cache, Config, Core, Http, Routing, Session |
| `Assets` | Css, JavaScript, Images, Metadata |
| `Build` | Database, Release, Files, Tooling |
| `Browser` | Manga, Navigation, Search, Profile, Chinois, Feedback, Assets |
| `Http` | Cases, Support, Auth, Navigation, Requests, Assets, reports |
| `Support` | Bootstrap commun des tests de domaine et du framework |

Les lanceurs restent dans `Http` et `Browser`. Les rapports HTTP sont générés dans `Http/reports`.

```text
composer check           # PHPStan, HTTP et régressions
composer browser-tests   # Scénarios Edge
composer tests:manga     # Tests manga
composer tests:search    # Recherche et protocole SPA
composer tests:assets    # CSS, JavaScript et images
composer check:all       # Ensemble des contrôles du projet
```

Pour lancer un scénario seul :

```text
php tests/Domain/Manga/manga-recommendations.php
```
