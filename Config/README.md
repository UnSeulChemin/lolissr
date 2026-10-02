# Configuration de LoliSSR

Voir aussi l'[architecture de l'application](../App/README.md) et le
[fonctionnement du framework](../Framework/README.md).

Ce dossier décrit les options de l'application, ses routes et les feuilles de style propres aux pages. Les valeurs locales restent dans `.env` ; le modèle est [`.env.example`](../.env.example).

## Chargement

Les extensions PHP requises sont déclarées dans `composer.json`, et non dans
les tableaux de ce dossier. `intl` sert à la translittération des sections HSK ;
`gd` sert à la génération des images de profil. Cette génération exige aussi
le support WebP sans perte, vérifié par son script.

Au démarrage HTTP, `Bootstrap::run()` charge l'environnement et vide la configuration en mémoire. Il utilise le cache compilé si son empreinte est valide ; sinon, il appelle `EnvironmentValidator` et charge la configuration normalement. Pour les scripts, `Bootstrap::loadEnvOnly()` charge l'environnement et exécute toujours la validation complète. Une configuration invalide arrête le démarrage ; la réponse HTTP reste générique et les détails sont journalisés.

Les tableaux PHP sont ensuite chargés à la demande par `config('fichier.cle', valeurParDefaut)`, puis mémorisés pendant la requête. Par exemple :

```php
$pagination = config('app.pagination', 8);
$sessionName = config('session.name', 'APP_SESSION');
```

`Config::clear()` vide cette mémoire. Ce n'est ni le cache de données de `Framework\Cache\Cache`, ni l'artefact compilé du bootstrap. Les nouvelles requêtes relisent l'environnement ; une modification d'une variable suivie invalide l'artefact et rétablit le chargement normal jusqu'à sa reconstruction avec `composer bootstrap:cache`.

Un fichier absent ou une clé absente renvoie la valeur par défaut propre à chaque appel. Un tableau vide, une valeur `null`, `false` ou `0` explicitement déclarés sont conservés. Un fichier de configuration existant doit retourner un tableau : un autre type provoque une exception au chargement comme à la compilation.

Les valeurs du `.env` remplacent les variables existantes pendant son utilisation. `Env::clear()` et le rechargement restaurent les valeurs antérieures de l'environnement du processus, de `$_ENV` et de `$_SERVER`, chacune séparément. Les variables initialement absentes sont supprimées. Une valeur commençant par un guillemet simple ou double doit se terminer par le même guillemet ; sinon, le chargement échoue en indiquant la ligne, sans exposer la valeur. Les guillemets préservent le contenu littéral, sans interpolation ni décodage des échappements.

`routes.php` est un cas distinct : il retourne une fonction d'enregistrement des routes, pas un tableau à lire avec `config()`.

## Variables obligatoires

Ces variables doivent être présentes et non vides. Les exemples ne sont pas des valeurs de secours.

| Variable | Exemple / contrainte |
|---|---|
| `APP_NAME` | `LoliSSR` |
| `APP_ENV` | `local`, `testing` ou `production` |
| `APP_BASE_URI` | `/lolissr` ou `/` ; pas de slash final sauf pour `/`, ni de segment `.` ou `..` |
| `APP_TIMEZONE` | Identifiant reconnu, par exemple `Europe/Paris` |
| `DB_HOST` | `localhost` |
| `DB_PORT` | Entier de 1 à 65535 |
| `DB_NAME` | Nom de la base |
| `DB_USER` | Utilisateur de connexion |

Le mode `testing` impose des transactions en lecture seule sur les connexions créées par `Framework\Database\Database`. Il ne sélectionne pas automatiquement une autre base de données.

## Options et valeurs par défaut

Les valeurs ci-dessous sont celles du code lorsque la variable est absente. Le fichier `.env.example` peut proposer des valeurs différentes adaptées au développement.

| Fichier | Variable | Défaut | Unité / rôle |
|---|---|---|---|
| `app.php` | `APP_VERSION` | `1.0.0` | Version affichée |
| `app.php` | `APP_DEBUG` | `false` | Informations de développement |
| `app.php` | `PROFILER_ENABLED` | `false` | Mesures ; démarrage du profiler également conditionné au debug |
| `app.php` | `APP_PAGINATION` | `8` | Éléments par page |
| `app.php` | `TRUST_PROXY` | `false` | Prise en compte du protocole transmis par un proxy |
| `session.php` | `SESSION_NAME` | `APP_SESSION` | Nom de session ; lettres, chiffres, tirets et underscores |
| `database.php` | `DB_PASS` | chaîne vide | Mot de passe, vide autorisé |
| `database.php` | `DB_CHARSET` | `utf8mb4` | Jeu de caractères de connexion |
| `database.php` | `DB_SLOW_QUERY_THRESHOLD` | `50` | Millisecondes |
| `cache.php` | `CACHE_ENABLED` | `false` | Cache de données |
| `cache.php` | `CACHE_TTL` | `300` | Secondes ; un appel peut fournir son propre TTL |
| `log.php` | `LOG_ENABLED` | `true` | Journalisation |
| `log.php` | `LOG_RETENTION_DAYS` | `14` | Jours ; nettoyage déclenché par les écritures, au maximum une fois par heure |
| `upload.php` | `UPLOAD_MAX_SIZE` | `5242880` | Octets, soit 5 Mio |
| `upload.php` | `UPLOAD_MAX_WIDTH` | `10000` | Pixels |
| `upload.php` | `UPLOAD_MAX_HEIGHT` | `10000` | Pixels |
| `upload.php` | `UPLOAD_MAX_PIXELS` | `50000000` | Nombre total de pixels |
| `upload.php` | `UPLOAD_ALLOWED_EXT` | `jpg,jpeg,png,webp` | Extensions séparées par des virgules |
| `upload.php` | `UPLOAD_ALLOWED_MIME` | `image/jpeg,image/png,image/webp` | Types MIME séparés par des virgules |
| `routes/auth.php` | `REGISTRATION_ENABLED` | `false` | Routes d'inscription hors production |
| `routes/sql.php` | `SQL_TOOL_ENABLED` | `false` | Routes de l'outil SQL hors production |

### Validation

- Écrire de préférence les booléens sous la forme `true` ou `false`. Les valeurs reconnues par le filtre PHP (`1/0`, `yes/no`, `on/off`) sont aussi acceptées. Une valeur explicitement vide ou non reconnue est refusée pour les options booléennes validées.
- Pagination, port, seuil SQL, limites d'upload, TTL et rétention des logs doivent être des entiers strictement positifs lorsqu'ils sont fournis.
- Les listes d'upload absentes utilisent leurs valeurs par défaut. Une liste explicitement vide est refusée. Espaces, casse et doublons sont normalisés une seule fois au chargement de `upload.php`.
- La validation des listes garantit qu'elles ne sont pas vides ; elle ne vérifie pas à elle seule la correspondance entre chaque extension et chaque type MIME.
- Le nom de session est vérifié lors de l'ouverture de la session.
- En production, `APP_DEBUG`, `PROFILER_ENABLED`, `SQL_TOOL_ENABLED` et `REGISTRATION_ENABLED` doivent être désactivés.

Les limites applicatives d'upload ne remplacent pas les limites de réception définies dans la configuration PHP du serveur.

Si un TTL de cache dépasse la durée représentable, son expiration est plafonnée à
`PHP_INT_MAX`, sans débordement. Cela vaut pour `CACHE_TTL` et les TTL fournis à
`Cache::remember()` ; l'invalidation explicite reste possible.

## Manifeste JavaScript

Le manifeste généré `javascript.php` contient l'entrée, les préchargements et les
fichiers publiés. Les empreintes des sources sont générées séparément dans
`scripts/Assets/javascript-sources.php`, pour les vérifications du build ; elles
ne sont pas chargées par les pages ni intégrées au cache du bootstrap. Les deux
fichiers sont régénérés par `composer js:build` (ou `composer assets:build`).
Le test du bundle vérifie leur correspondance et les empreintes des sources.

## Styles par page

[`styles.php`](styles.php) associe des fichiers relatifs à `public/css/` aux vues relatives à `App/Views/`, sans extension `.php` :

```php
'page/exemple.css' => ['pages/exemple/index'],
'components/exemple.css' => ['pages/exemple/'],
```

Un chemin de vue sans slash final correspond exactement à cette vue. Un chemin terminé par `/` couvre toutes les vues de ce répertoire. L'ordre des entrées détermine l'ordre de chargement des feuilles : il compte pour la cascade CSS.

`App\Support\Assets\PageStyles` lit ce tableau via `config('styles', [])`. Le rendu initial et le routeur SPA utilisent cette sélection. Les styles communs restent dans `public/css/app.css`.

Pour ajouter une feuille spécifique, créer le fichier CSS puis ajouter son association ici. Préserver l'ordre des dépendances et les règles partagées ; les pages de chinois utilisent actuellement ensemble les feuilles vocabulaire et grammaire.

## Routes

[`routes.php`](routes.php) charge les routes d'authentification puis enregistre les autres familles dans un groupe protégé par `AuthMiddleware`. Les protections CSRF et les contraintes JSON restent déclarées sur les routes ou leurs groupes.

Les routes figurine, nendoroid et peluche utilisent [`CollectionRouteRegistrar`](../App/Http/Routing/CollectionRouteRegistrar.php) :

```php
CollectionRouteRegistrar::register(
    $router,
    'figurine',
    FigurineController::class,
    FigurineAjaxController::class,
    withLinks: true
);
```

Le préfixe et les contrôleurs sont propres à chaque collection. `withLinks: true` ajoute la route `lien`, actuellement réservée aux figurines. Le groupe d'authentification du parent est hérité ; le helper ne l'ajoute pas lui-même.

Le contrôleur principal doit fournir `index`, `waifus`, `edit`, `update`, `showWaifu`, `create` et `store`, ainsi que `links` si cette option est activée. Le contrôleur AJAX doit fournir `delete`, `waifusPage`, `search` et `updateCollectStatus`.

Modifier le helper pour un changement commun aux trois collections. Garder les comportements spécifiques dans leur fichier de routes. Les routes manga, chinois, profil et authentification restent séparées, car leur structure diffère.

L'ordre de déclaration fait partie du comportement du routeur : une route dynamique déclarée avant une route statique peut rester prioritaire. Ne pas réordonner les routes comme un simple changement de présentation.

## Cache du bootstrap au déploiement

Après installation sur le serveur cible, configuration du `.env` et compilation des assets :

```sh
composer bootstrap:cache
```

Cette commande compile les tableaux de configuration et la collection de routes
(expressions régulières, ordre et middlewares compris) dans
`storage/bootstrap/compiled.php`. Les closures de groupes sont exécutées au build ;
une closure utilisée comme action de route doit être remplacée par un contrôleur
avant d'activer ce cache.

Le `.env` reste chargé à chaque requête. La validation complète est exécutée à la
compilation et lors du chargement normal ; un cache valide réutilise cette validation
après vérification des empreintes de l'environnement et du validateur. Une modification d'une variable
consultée lors de la compilation, y compris une variable système, invalide le cache
et rétablit le chargement normal. Les erreurs de configuration de production restent
donc détectées. Le cache absent ou illisible utilise également le chargement normal.

Reconstruire le cache après tout changement du code, des routes, de la configuration
ou des assets. Pour le désactiver avant une modification ou en développement :

```sh
composer bootstrap:clear
```

L'artefact contient la configuration privée de la machine : il est ignoré par Git
et absent des archives de livraison. Il doit être construit sur la cible par le compte
qui exécute PHP (fichier `0600`, répertoire `0700` sur Unix). Si OPcache ne vérifie pas
les dates des fichiers, recharger les workers PHP après reconstruction ou suppression.
La commande CLI ne peut pas invalider le cache OPcache d'un autre processus serveur.

Mesure locale reproductible, sans accès à la base :

```sh
php tests/Framework/bootstrap-cache.php --benchmark
```

## Ajouter une option

1. Déclarer la variable et son exemple dans `.env.example`, sans donnée sensible réelle.
2. Ajouter sa lecture dans le fichier `Config` concerné, avec un défaut si elle est facultative.
3. Ajouter les contraintes nécessaires dans `EnvironmentValidator` et préciser si elle est obligatoire.
4. Lire l'option avec `config()` dans le code consommateur et mettre à jour cette documentation.

Les scripts de sauvegarde et de publication ont leurs propres options, notamment `MYSQLDUMP_PATH`. Les identifiants `HTTP_TEST_USERNAME` et `HTTP_TEST_PASSWORD` concernent l'outillage HTTP ; voir [`docs/guide.md`](../docs/guide.md).
