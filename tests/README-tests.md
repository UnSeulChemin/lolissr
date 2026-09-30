# LoliSSR HTTP Tests

Suite de tests HTTP utilisée pour vérifier les principales routes de l'application.

## Prérequis

Les tests sont exécutés avec un utilisateur connecté.

## Vérifications

- Routes web
- Routes AJAX
- Réponses JSON
- Fragments HTML
- Codes HTTP
- Pages d'erreur

## Exécution

`php tests/run-tests.php` nécessite Apache local et un compte existant, configuré
avec `HTTP_TEST_USERNAME` et `HTTP_TEST_PASSWORD` dans `.env`.

Les cas ne réalisent pas de modification volontaire des collections ni d'upload réel.
Les lectures du profil n'attribuent plus de récompenses XP.
La connexion peut toutefois modifier les tentatives de connexion et renouveler un
hash de mot de passe ; sessions, caches, logs et rapports peuvent être écrits.
Utiliser une base locale de test pour isoler ces effets.

Le mode `testing` du lanceur CLI ne change pas l'environnement du serveur Apache.
Il ne garantit donc pas à lui seul une base en lecture seule pour les requêtes HTTP.

## Régressions sans base applicative

```powershell
composer regression-tests
```

- `profile-read-only.php` utilise SQLite en mémoire, avec tous les paliers atteints
  et des récompenses manquantes. La base est ensuite verrouillée en lecture seule :
  deux lectures du profil doivent conserver les XP et les récompenses existantes.
- `css-bundle.php` vérifie que le bundle est à jour et conserve les chaînes et les
  espaces significatifs du CSS.
- `page-styles.php` vérifie les dépendances CSS par page et leurs URL versionnées.
- `asset-versions.php` vérifie le manifeste des versions utilisé en production.
- `javascript-bundle.php` vérifie que le bundle correspond aux sources, que ses
  fichiers existent et que les layouts local/production sélectionnent le bon script.

`composer http-tests` lance aussi `tests/spa-http.php` : contenu et métadonnées des
fragments comparés aux pages complètes, résultats de recherche globale comparés
aux six routes existantes et rejet des paramètres invalides.

Vérification du cache, du rendu et de la recherche au clavier dans Edge :

```powershell
php tests/run-page-styles-browser.php http://localhost/lolissr tests/spa-browser.js
```

Le routeur et le préchargement demandent `X-Page-Format: fragment`. Sans cet en-tête,
les réponses JSON conservent le document complet pour les anciens clients.
L'invalidation d'une section reste récursive ; `{descendants: false}` cible seulement
une page et ses variantes de paramètres, notamment l'accueil.

## CSS commun

```powershell
composer assets:build
```

Après modification du CSS ou du JavaScript, reconstruire `public/css/app.bundle.css`
et `Config/assets.php` avec cette commande. `composer css:build` reste disponible
pour reconstruire uniquement le CSS pendant le développement.
Le layout utilise ce fichier en production, avec une URL versionnée par son contenu.
En local, il garde `app.css` et ses imports. Les scripts `publish-git.php` et
`build-release.php` reconstruisent automatiquement le bundle et le manifeste avant
publication ou archive. Les deux fichiers générés doivent accompagner les déploiements
manuels. En production, les versions sont lues dans le manifeste ; en local, les
hashes sont calculés une seule fois par fichier et par requête.

Comparaison des règles CSS interprétées par Edge (Apache et Edge requis) :

```powershell
php tests/run-page-styles-browser.php http://localhost/lolissr tests/css-bundle-browser.js
```

## Rattrapage des anciennes récompenses

```powershell
php scripts/backfill-achievement-xp.php USER_ID
php scripts/backfill-achievement-xp.php USER_ID --apply
```

La première commande consulte les XP existantes en lecture seule. La seconde attribue
les succès manquants selon les statistiques actuelles, dans une transaction.
Elle conserve la protection contre les doubles récompenses. Les collections étant
partagées dans le modèle actuel, préciser l'identifiant du compte concerné.
Ce rattrapage ne s'exécute jamais automatiquement à l'affichage d'une page.

## Récompenses groupées et chargement JavaScript

```powershell
php tests/achievement-xp.php
php tests/run-page-styles-browser.php http://localhost/lolissr tests/route-initializers-browser.js
```

Le test XP utilise la connexion MySQL configurée et uniquement des tables temporaires
propres à cette connexion, qui masquent les tables réelles. Il vérifie les paliers
déjà attribués, les nouveaux paliers, les niveaux, l'isolation des comptes et le rollback.
Il nécessite le droit de créer des tables temporaires ; aucune donnée de compte réel
n'est modifiée. Le test navigateur vérifie le ciblage des actions, les imports en
parallèle, l'ordre d'initialisation et l'isolation des erreurs de chargement.
Les compteurs de personnalisation sont vérifiés par `tests/profile-read-only.php`.

## Images du profil et notification flash

```powershell
composer images:build
php tests/profile-images.php
php tests/run-page-styles-browser.php http://localhost/lolissr tests/flash-toast-browser.js
```

PHP GD avec WebP et la connexion MySQL sont requis. La commande remplace les PNG
statiques du profil de plus de 200 Ko par des WebP directement dans `thumbnail`.
Les PNG convertis et les WebP existants sont redimensionnés proportionnellement
si nécessaire : côté maximal de 512 px pour avatars/cadres et 2160 px pour
bannières. La transparence est conservée. L'encodage WebP est sans perte après
redimensionnement ; la réduction de dimensions retire toutefois des détails.
Seuls les résultats plus petits sont conservés. Les animations PNG/WebP sont ignorées.

La génération utilise un fichier temporaire, validé avant remplacement. Après une
interruption, un WebP déjà généré à l'identique est accepté ; une collision avec
une autre image est refusée. `php tests/profile-image-builder.php` vérifie la
reprise, les collisions, les dimensions et la transparence sur des fichiers isolés.

Les extensions des images concernées sont mises à jour dans `users`, puis les
PNG remplacés sont supprimés. Les anciens sous-dossiers `optimized` sont vidés
des fichiers migrés et supprimés lorsqu'ils sont vides. Le catalogue découvre
les WebP directement, sans négociation de format par Apache.

Les images restent exclues de Git et des archives de release. Lors du déploiement,
exécuter `composer images:build` sur le serveur pour convertir ses PNG et mettre
à jour sa base, ou transférer les WebP puis lancer la commande pour migrer les
extensions enregistrées. Supprimer également les PNG remplacés sur le serveur
si le transfert des WebP est effectué manuellement. Le test images consulte la
base en lecture seule et vérifie que les images des comptes existent.

## Bundle JavaScript de production

```powershell
composer js:install
composer assets:build
php tests/run-javascript-browser.php http://localhost/lolissr
```

L'installation télécharge le binaire officiel esbuild 0.28.2 adapté au système,
vérifie son intégrité SHA-512 et le place dans `storage/tools` (ignoré par Git).
Node.js n'est pas nécessaire. Elle nécessite une connexion au registre npm.
La construction suivante fonctionne hors ligne. `composer js:build` reconstruit
seulement le JavaScript ; utiliser `composer assets:build` pour actualiser aussi
le manifeste des versions. Publication Git et création de release appellent ce
dernier automatiquement, et nécessitent donc l'installation locale d'esbuild.

En production, le layout utilise `Config/javascript.php` et les fichiers hashés
de `public/js/dist`. En local, il conserve les modules sources. Le code partagé
et les pages utilisent le même graphe de modules ; les imports des pages restent
différés grâce au [code splitting d'esbuild](https://esbuild.github.io/api/#splitting).
Seuls les modules nécessaires au démarrage sont préchargés. Les anciens chunks
sont conservés par les builds locaux. Après chaque déploiement sur le serveur,
exécuter `composer js:prune` : cette commande observe les fichiers inactifs, puis
les supprime lors d'une exécution ultérieure après sept jours. Le suivi réside dans
`storage/javascript-retention.json`, propre au serveur et non versionné.
Conserver ce fichier et les anciens fichiers de `public/js/dist` entre déploiements :
ne pas purger le dossier avant d'y ajouter les nouveaux bundles. Le nettoyage peut
aussi être exécuté périodiquement. Un onglet utilisant une version retirée depuis
plus de sept jours peut nécessiter un rechargement. Le manifeste et les fichiers
générés sont versionnés, donc esbuild n'est pas nécessaire sur le serveur pour
servir le site. Le test navigateur vérifie le bundle, les modales, les changements
de route et le respect de `navigator.connection.saveData`.

Les manifestes `Config/javascript.php` et `Config/assets.php` sont publiés par
remplacement atomique dans leur dossier : une écriture interrompue ne tronque pas
le manifeste précédent. Une sortie identique n'est pas réécrite. La vérification
`tests/atomic-file.php` est incluse dans `composer regression-tests`.

```powershell
php tests/run-page-styles-browser.php http://localhost/lolissr tests/spa-lifecycle-browser.js
php tests/asset-cache-http.php http://localhost/lolissr
```

Le premier test couvre la navigation pendant le démarrage (modules globaux et
modules de route), l'absence de double initialisation et l'expiration fixe du
cache SPA. Réafficher une réponse en cache conserve son âge initial ; seule une
nouvelle réponse reçue bénéficie d'une nouvelle durée de cache.

Le second nécessite `mod_headers` activé et Apache redémarré. Le fichier
`public/js/dist/.htaccess` réserve `public, max-age=31536000, immutable` aux bundles
dont le nom contient un hash. Il couvre les entrées, les chunks et les réponses
304, sans donner cette politique aux sources ni aux erreurs 404. Déployer aussi
ce fichier `.htaccess` avec les bundles.

## Compression HTTP et index redondants

`public/.htaccess` active gzip pour HTML, CSS, JavaScript, JSON et SVG lorsque
`mod_deflate` et `mod_filter` sont chargés. Le serveur conserve la négociation
`Accept-Encoding` et le cache varie sur cet en-tête. Les images WebP sont exclues.
Avec Wamp, activer ces deux modules puis redémarrer Apache depuis Wamp si le
terminal ne dispose pas des droits de contrôle du service Windows.

```powershell
composer db:deduplicate-indexes
composer db:deduplicate-indexes -- --apply
```

Sans `--apply`, la commande affiche uniquement le plan. Elle vérifie les colonnes,
l'ordre, les préfixes et le type des cinq paires d'index connues avant toute
suppression. Les contraintes uniques `uq_*_slug_numero` sont conservées. Une
relance ignore les doublons déjà supprimés. Exécuter séparément sur chaque base
à migrer ; cette opération n'est pas déclenchée par la construction des assets.

### Release et historique SPA

La release installe ses dépendances depuis `composer.lock` avec `--no-dev` dans
son dossier temporaire. Composer doit être disponible dans le PATH. Le dossier
`vendor` de développement reste intact ; son autoloader n'est pas copié.

Vérifications ciblées :

```sh
php tests/production-dependencies.php
php tests/javascript-retention.php
php tests/run-page-styles-browser.php http://localhost/lolissr tests/scroll-history-browser.js
```

Le test de rétention fait partie de `composer regression-tests`. Le test de
production lance Composer dans un dossier isolé. Le test navigateur couvre
précédent/suivant et plusieurs visites de la même URL avec des positions distinctes.

### Vérification complète

```sh
composer check:all
```

Cette commande lance `composer check`, puis les six suites navigateur (CSS,
SPA, initialisation des routes, démarrage, historique et bundle de production).
Apache doit servir le projet et Microsoft Edge doit être installé à l'emplacement
Windows utilisé par `tests/run-page-styles-browser.php`. Une suite en échec
interrompt la commande avec un code non nul.

Pour les navigateurs seuls, avec une URL différente :

```sh
composer browser-tests -- http://localhost/lolissr
```

Les régressions `release-archive.php` et `cache-expiration-race.php` sont incluses
dans `composer check`. Elles vérifient la conservation du ZIP précédent en cas
d'échec et la suppression conditionnelle du cache après remplacement concurrent.
La release prépare et vérifie un ZIP temporaire avant de remplacer l'archive
existante ; elle ne supprime jamais cette dernière pour forcer un remplacement.

### Messages SPA et flashcards obsolètes

`tests/flash-toast.php` vérifie que le préchargement ne consomme ni succès ni
 erreur en session. `flash-feedback-browser.js`, inclus dans `composer browser-tests`,
vérifie le rafraîchissement d'une page préchargée portant un message en attente,
l'affichage unique du message et le traitement par lot des identifiants de
flashcards disparus. Ces vérifications font donc partie de `composer check:all`.

Le préchargement préserve également `errors` et `old` en session, sans les inclure
dans le formulaire préchargé. Leur présence impose une requête fraîche à
l'ouverture réelle ; `tests/flash-toast.php` vérifie leur consommation unique.
`navigation-cancel-browser.js` couvre le retour sur la page courante pendant un
chargement et une réponse tardive pendant une nouvelle navigation. Il est inclus
dans `composer browser-tests` et `composer check:all`.

Les builds CSS, JS, assets, releases et le nettoyage prennent le même verrou
`storage/.build.lock`. Un second processus échoue avec un message explicite avant
de modifier les sorties. Le verrou est libéré à la fin du processus ; ne pas
supprimer son fichier pendant un build. Les releases utilisent également un dossier
 temporaire unique. `tests/build-lock.php` vérifie l'exclusion entre processus.
