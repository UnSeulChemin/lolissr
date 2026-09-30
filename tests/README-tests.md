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
sont conservés lors des reconstructions pour les onglets encore ouverts ; éviter
de les supprimer pendant un déploiement actif. Le manifeste et les fichiers
générés sont versionnés, donc esbuild n'est pas nécessaire sur le serveur pour
servir le site. Le test navigateur vérifie le bundle, les modales, les changements
de route et le respect de `navigator.connection.saveData`.

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
