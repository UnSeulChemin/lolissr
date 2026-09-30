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
