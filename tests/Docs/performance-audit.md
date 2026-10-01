# Audit des performances — 1er octobre 2026

Analyse du code PHP, des principales requêtes de lecture, du cache, du graphe
JavaScript, du chargement des styles/images et des réponses HTTP locales.
Les mesures ci-dessous décrivent cette installation locale ; ce ne sont pas
des mesures de charge ni une validation du serveur de production.

## Modifications appliquées

- Le changement de statut de lecture d'un artbook réutilise l'état retourné
  par son unique lecture verrouillée : une requête SQL évitée, sans retirer
  le verrou ni changer le calcul des récompenses.
- La lecture d'une série manga sélectionne les huit champs utilisés par ses
  cartes, en conservant les agrégats. Sur les 100 derniers mangas locaux,
  les valeurs de ces champs représentent 7 703 octets contre 11 448 pour les
  lignes complètes (hors agrégats et protocole SQL). La régression de projection
  compare les DTO avec ceux des lectures complètes.
- Les sections HSK calculent leur slug une seule fois avec un translittérateur
  partagé pendant leur construction. Les collisions, noms réservés, accents,
  caractères chinois et sections vides sont couverts par `app-boundaries.php`.

- Quatre listes (figurines, nendoroids, peluches, artbooks) sélectionnent les
  sept colonnes utilisées par leurs cartes. Les commentaires, dates et autres
  champs de détail ne sont plus transférés puis hydratés inutilement.
- Les deux requêtes manga du dashboard sélectionnent uniquement les champs
  des cartes et le compteur de tomes lorsqu'il est nécessaire.
- Suppression de `Model::find()`, sans référence dans le PHP de l'application,
  du framework, de la configuration, des scripts et des tests.
- `tests/Domain/collection-projections.php`, intégré à `composer regression-tests`,
  compare les DTO des listes avec ceux de modèles complets sur des fixtures
  SQLite. Il couvre l'ordre, la pagination, les couvertures absentes, les
  commentaires volumineux exclus et les cartes manga du dashboard.

Échantillon : les 20 dernières lignes par table, ou toute la table si elle
en contient moins. Somme des tailles des valeurs de champs avant/après
projection, hors protocole SQL et allocation des objets PHP :

| Table | Colonnes avant → après | Octets de champs avant → après |
| --- | --- | --- |
| Figurine | 15 → 7 | 818 → 446 |
| Nendoroid | 13 → 7 | 1 701 → 860 |
| Peluche | 13 → 7 | 285 → 135 |
| Artbook | 14 → 7 | 669 → 383 |
| Manga | 16 → 6 | 2 247 → 1 376 |

La réduction est d'environ 39 à 53 % sur ces valeurs. Le gain absolu reste
petit sur la base actuelle ; aucun gain équivalent sur le temps total des
pages n'est revendiqué. Aucun index ni contenu de la base réelle n'a été modifié.

## Priorité : compression et cache HTTP du serveur

Les réponses locales vérifiées ne contiennent pas `Content-Encoding: gzip`
pour le CSS avec `Accept-Encoding: gzip`, ni le `Cache-Control` longue durée
attendu pour les bundles JavaScript hashés. `tests/Http/asset-cache-http.php` échoue.

Les règles existent déjà dans `public/.htaccess` et `public/js/dist/.htaccess`.
La configuration Apache sur disque active `headers`, `deflate` et `filter` ;
`httpd -t -D DUMP_MODULES` les liste également. Cela ne prouve pas que le
processus Apache actuellement en service a rechargé cette configuration.

Prochaine intervention : vérifier l'instance/configuration utilisée par Wamp,
redémarrer Apache dans une fenêtre adaptée, puis relancer :

```powershell
php tests/Http/asset-cache-http.php http://localhost/lolissr
curl.exe -I -H "Accept-Encoding: gzip" http://localhost/lolissr/css/app.bundle.css
```

Potentiel mesuré par compression locale gzip niveau 6 (pas un transfert HTTP) :

| Fichier | Brut | Gzip |
| --- | --- | --- |
| `css/app.bundle.css` | 40 828 octets | 8 356 octets |
| Entrée JS active `app-SQYYVRIP.js` | 21 404 octets | 6 652 octets |

## Images

Les huit plus grosses images sont des bannières de profil WebP, de 1,69 à
2,13 Mio chacune. Le format WebP ne suffit donc pas à garantir un fichier léger.
Les galeries et beaucoup de récompenses utilisent déjà le chargement différé.

Piste suivante : variantes plus petites pour les sélecteurs et encodage avec
perte après comparaison visuelle. Les originaux n'ont pas été réencodés dans
cet audit ; le compromis de qualité reste à évaluer.

## Points déjà en place

- Les 111 modules JS sources sont accessibles depuis `app.js` via leurs
  chemins relatifs littéraux, imports différés inclus : aucun fichier orphelin
  identifié par cette analyse. Cela ne prouve pas l'utilisation de chaque export.
- Les cinq paires connues d'index redondants sont déjà nettoyées, vérification
  en lecture seule avec `scripts/Database/deduplicate-indexes.php`.
- Cache applicatif activé ; l'environnement local utilise volontairement les
  sources. Le bundle et le manifeste de production ont leurs tests dédiés.
- Recherche globale groupée, délai de saisie et annulation des requêtes ;
  préchargement limité à trois requêtes et désactivé avec `saveData`.
- Pagination des collections, lots de 50 flashcards et limite de 500 lignes
  dans l'outil SQL déjà présents.
- Les anciens chunks JS sont conservés pour les onglets encore ouverts :
  leur présence seule ne justifie pas une suppression immédiate.

## Vérifications

PHPStan niveau 8, régressions et tests SPA HTTP réussis ; 64 routes HTTP sur
64 réussies. Le test de projection utilise uniquement SQLite en mémoire.
Les huit suites navigateur passent après exécution d'Edge hors du bac à sable
(le lancement initial dans le bac à sable ne produisait aucun DOM).
Le contrôle HTTP du cache des bundles reste en échec sur Apache local.
