
## Sécurité

### SEC-04 — Erreurs précoces hors du middleware de sécurité

**Fichiers :** `Framework/Application/Bootstrap.php:95`, `Framework/Application/HttpKernel.php`, `Framework/Http/Middleware/SecurityHeadersMiddleware.php`.

`$request->postAll()` analyse et rejette le JSON avant `$kernel->boot()`, qui applique les en-têtes de sécurité.

**Reproduction :** POST sur `/lolissr/connexion`, `Content-Type: application/json`, `Accept: application/json`, corps `{` : réponse 400 correcte, mais sans CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` ou identifiant de requête dans les en-têtes. Le chemin est également pertinent pour les dépassements de limite JSON.

**Impact :** couverture incomplète des protections HTTP sur les réponses d'erreur. La réponse testée reste un JSON générique ; aucune XSS n'a été démontrée. L'en-tête Apache `Server` révèle aussi les versions PHP/Apache sur cette réponse.

**Correction :** appliquer les en-têtes dès que la configuration et le contexte de requête sont disponibles, avant l'analyse du corps, sans avancer inutilement l'ouverture de session. Prévoir une couverture minimale indépendante pour les erreurs d'amorçage. Tester 400/413 et les erreurs de configuration ; traiter la divulgation de version dans la configuration Apache.




### SEC-05 — La zone « dev » reste enregistrée en production

**Fichiers :** `Config/routes/admin.php:20`, `App/Http/Middleware/AdminOwnerMiddleware.php:19`, `App/Services/Admin/MaintenanceJob.php`.

Le groupe `admin/dev` n'a pas de condition d'environnement. Il expose notamment reset, optimisation d'images, purge forcée JS, migrations, sauvegarde et modification d'XP.

**Protections existantes :** authentification, compte strictement égal à 1, CSRF sur les POST et liste fermée des commandes. Ce n'est pas une élévation de privilège constatée. Certaines opérations sont utiles en production ; leur disponibilité peut être intentionnelle.

**Amélioration :** distinguer les opérations nécessaires en production des opérations de développement, puis conditionner les secondes à une configuration explicite. Les tests d'accès actuels vérifient correctement que les autres comptes, y compris avec `is_admin = true`, sont refusés.

## Performances

### PERF-01 — Encadrer la recherche avant d'ajouter des index

**Fichiers :** `App/Http/Controllers/Search/GlobalSearchController.php:35`, `App/Repositories/Collections/Concerns/SearchesCollectibles.php:27`, repositories de recherche Manga/Artbook/Chinois.

La recherche globale lance les six catégories et les filtres de recommandations. `q` est contrôlé comme chaîne puis nettoyé, mais n'a pas de longueur maximale explicite dans le contrôleur. Les paramètres SQL sont liés : ce constat n'est pas une injection SQL.

Les motifs `%texte%` limitent l'utilisation des index B-tree pour la partie textuelle. Les caractères `%` et `_` saisis restent également des jokers SQL dans plusieurs recherches ; décider s'ils doivent être interprétés littéralement.

**Mesure :** `php scripts/Tools/profile-search.php 1 a absent-search-987654 --limit=5`, 10 mesures après échauffement :

| Catégorie | Médiane pour `a` | Plan local notable |
| --- | ---: | --- |
| Filtres recommandations | 2,42 ms | Une requête de révision par clé primaire |
| Manga | 3,33 ms | `ALL`, 198 lignes estimées, `Using filesort` |
| Artbook | 3,60 ms | Index propriétaire, `Using filesort` |
| Chinois | 0,89 ms | Parcours d'index borné par les résultats |
| Figurine | 2,57 ms | Index propriétaire, `Using filesort` |
| Nendoroid | 2,83 ms | `ALL`, 17 lignes estimées, `Using filesort` |
| Peluche | 2,35 ms | Index propriétaire, `Using filesort` |

Les scans sur des petites tables peuvent être le meilleur choix de l'optimiseur. Ces mesures ne démontrent pas un problème urgent, ni le comportement avec des milliers de lignes. Les temps incluent PHP/DTO et ne sont pas seulement des temps SQL ; les catégories ont été mesurées séparément.

**Actions :** borner `q` côté serveur, par exemple à 200 caractères ; définir la sémantique des jokers ; profiler avec des volumes représentatifs avant tout ajout d'index. Pour de grandes collections, évaluer recherche par préfixe ou index dédié en conservant les besoins de recherche par sous-chaîne. Ne pas remplacer automatiquement `%texte%` par FULLTEXT : les résultats changeraient.

### Optimisations déjà présentes à conserver

- Projection légère des listes et résultats de recherche, pagination et lots de flashcards bornés.
- Statistiques/XP traitées en lots ; tests contre les récompenses répétées et isolation des comptes.
- Cache de recommandations par compte et révision, invalidation sélective des changements de manga.
- Verrouillage du cache et protection contre les producteurs périmés ; verrous conservés intentionnellement.
- Bundles avec imports différés, cache LRU et annulation des navigations/recherches périmées.
- Miniatures, variantes grid et empreintes persistantes des images.

`composer doctor` observe 23 valeurs de cache et 33 clés avec verrous ; 10 clés n'ont pas de valeur. Elles ne sont pas des preuves de déchets supprimables : les verrous stables participent à la sécurité des accès concurrents. Le diagnostic ne relève aucun timeout de verrou dans les 1 570 requêtes enregistrées du journal de profilage examiné.

`deduplicate-indexes.php --check` trouve **0 index redondant éligible** sur la base locale.

La suite HTTP mesure une moyenne de **59,63 ms** sur ses 106 cas. Il s'agit d'un seul passage local exécuté pendant d'autres vérifications : ce chiffre ne sert pas de référence de production.

## Code mort et entretien

### DEAD-01 — Bundles retirés, conservation prévue

`public/js/dist/` contient 123 fichiers JS ; le manifeste actif en référence 58. Les 65 autres représentent **147 234 octets**, soit environ 144 Kio non compressés sur disque.

Ils ne sont pas dans le manifeste actuel, mais peuvent encore être utilisés par des pages ouvertes. Le mécanisme `JavaScriptRetention` conserve les fichiers pendant sept jours depuis leur première observation comme retirés sur le serveur concerné. La livraison neuve copie uniquement les bundles actifs.

**Action :** utiliser le mécanisme existant `composer js:prune` sur le serveur déployé, au rythme approprié. Aucun fichier n'a été supprimé pendant cet audit. La purge forcée demande un rechargement des anciennes pages et ne constitue pas une optimisation de téléchargement du bundle actif.

### MAINT-01 — `is_admin` est un champ hérité, pas un droit effectif

**Fichier :** `App/Models/User/User.php:11`.

Le champ reste hydraté et testé, mais les autorisations d'administration reposent sur `id === 1`. Ce choix est explicitement couvert par `tests/Domain/Auth/admin-access.php` : ne pas le remplacer silencieusement par `is_admin`.

**Action :** documenter ce modèle de propriétaire unique, ou préparer une migration de rôles si le besoin change. Retirer le champ exige d'examiner schéma, données et compatibilité ; sa seule absence dans une condition ne justifie pas une suppression immédiate.

### Résultat de la recherche de symboles

Les recherches d'occurrences couvrent App, Framework, Config, scripts, public et tests, en excluant les bundles générés. Aucun nom de méthode PHP n'a été trouvé sans autre occurrence ; même résultat dans les sources d'exécution sans les tests. Aucun module JS source n'a été trouvé sans référence textuelle à son nom de fichier, ni fonction JS exportée sans autre occurrence dans l'ensemble examiné.

Cette méthode est un filtre heuristique : un même nom peut exister dans plusieurs classes et masquer une méthode inutilisée. Elle ne prouve donc pas que tout le code est exécuté. Les imports dynamiques, routes, callbacks et API du framework exigent une analyse des appels avant suppression.

`ServiceProvider` et `ErrorController`, qui semblaient isolés dans un premier inventaire limité, sont bien appelés par `public/index.php:19`. Les scripts de maintenance sont des points d'entrée Composer ou des workers ; leur absence dans une requête web ordinaire ne les rend pas morts. Aucune suppression de source fiable n'est proposée sur la seule base de cet audit.

## Vérifications exécutées

| Vérification | Résultat |
| --- | --- |
| `composer validate --no-check-publish` | OK |
| `composer phpstan` | OK, niveau 8 et règles strictes |
| `composer doctor` | OK, configuration, assets, cache, MySQL, migrations et HTTP local |
| `composer regression-tests` | OK, toutes les étapes |
| `composer http-tests` | OK, 106/106 puis contrôles SPA/recherche et limite JSON |
| `composer browser-tests` | OK après relance hors sandbox |
| `php tests/Domain/Database/database-schema.php` | OK |
| `php scripts/Database/deduplicate-indexes.php --check` | OK, 0 index éligible |
| `php tests/Http/Auth/user-ownership.php` | OK, deux sessions et refus des accès étrangers |
| `php tests/Http/Assets/asset-cache-http.php` | OK |
| `php tests/Http/Assets/apache-portability.php` | OK, racine et dossier renommé |
| `php tests/Build/Release/production-dependencies.php` | OK, vendor de développement préservé |
| `composer dx:check` | OK, 649 sources, 0 à reformater |

Le premier lancement navigateur a échoué avant les assertions avec un crash GPU d'Edge dans le sandbox Windows. La relance autorisée hors sandbox a validé toutes les suites : ce premier échec n'est pas présenté comme un défaut applicatif.

**Limite des tests :** `thumbnail-delete-path.php` indique **0/2 contrôles de symlinks exécutés** sur cet environnement. Les tests ordinaires de confinement passent ; vérifier aussi les liens symboliques sur un environnement qui permet leur création.

Les contrôles de dépendances vérifient la livraison et l'autoload. Aucun audit CVE externe ni analyse de la configuration du serveur de production n'a été réalisé.

## Ordre conseillé des corrections

1. Retirer les fichiers d'exécution du suivi Git et versionner la protection des images.
2. Désactiver les listings Apache et couvrir les réponses d'erreur avec les en-têtes de sécurité.
3. Définir les commandes autorisées en production et borner la recherche côté serveur.
4. Entretenir la rétention des bundles et documenter le statut de `is_admin`.
5. Refaire les mesures sur des collections représentatives avant de modifier les requêtes ou index.
