
## 4. Priorité moyenne : recherche avec sous-chaînes

`App/Repositories/Collections/Concerns/SearchesCollectibles.php:27` effectue plusieurs `LIKE '%terme%'` reliés par OR. Les recherches manga et artbook utilisent aussi des sous-chaînes. Le LIMIT borne les résultats retournés ; il ne borne pas nécessairement les lignes examinées ni le coût du tri.

Action : examiner les plans SQL et la latence selon la taille des collections avec `profile-search.php`. Vérifier d'abord les index commençant par user_id. Étudier ensuite une recherche par préfixe ou un index de recherche dédié si les mesures le justifient. FULLTEXT ou préfixe changent les résultats, notamment pour les fragments, nombres, accents et textes chinois : pas de remplacement automatique sans définir le comportement attendu.

Le contrôleur global appelle les six services de collections et les filtres de recommandations pour chaque recherche. Le navigateur possède déjà un debounce de 200 ms, des annulations et un cache LRU de 20 entrées pendant 30 secondes : ces protections sont utiles, elles ne remplacent pas l'optimisation SQL.

## 5. Priorité basse : ancienne méthode de récompense utilisée seulement par les tests

`AchievementXpService::rewardAll()` (`App/Services/Profile/AchievementXpService.php:87`) n'a pas d'appel trouvé dans l'application ou les scripts ; deux appels subsistent dans `tests/Domain/Profile/achievement-xp.php`.

Statut : API utilisée uniquement par les tests, candidate au retrait, pas une suppression à faire aveuglément. Déterminer si elle constitue un point d'entrée souhaité. Sinon transférer les assertions d'idempotence vers les méthodes de récompense effectivement utilisées puis la supprimer. `reconcile()` existe pour la réconciliation, mais ses effets ne sont pas équivalents à un simple ajout de récompenses.

## 6. Priorité basse : anciens bundles JavaScript

Inventaire actuel : 99 fichiers JavaScript dans public/js/dist, dont 59 référencés par le manifeste actif et 40 hors manifeste, pour 86 057 octets cumulés hors manifeste.

Ces fichiers ne sont pas tous du code mort supprimable immédiatement : les anciennes pages ouvertes peuvent encore demander leurs chunks. Le projet possède déjà une rétention de sept jours dans `JavaScriptRetention` et `composer js:prune`. Utiliser ce mécanisme sur le serveur déployé. Le délai part de l'observation sur ce serveur, pas de la date locale du build. Aucune suppression effectuée pendant l'audit.

## 7. Duplication et optimisations déjà présentes

Les services de lecture Figurine, Nendoroid et Peluche restent très similaires dans leurs mappings et DTO. Une partie commune existe déjà dans `BuildsCollectionReadData`, `SearchesCollectibles` et les concerns de statistiques/écriture. Une généralisation supplémentaire servirait principalement la maintenance ; elle n'est pas prioritaire pour la performance et pourrait compliquer les types et les particularités des catégories.

Points positifs vérifiés : pagination des collections, projections SQL ciblées dans les recherches, agrégats regroupés du profil et du dashboard, caches bornés de recherche/préchargement, imports JavaScript différés, manifestes et cache persistant des empreintes d'images, cache compilé de configuration/routes, singleton de connexion DB. Ne pas supprimer les sources CSS/JS simplement parce que leurs bundles existent : elles servent aux builds.

Les sous-requêtes de `ownedTable()` protègent le périmètre de chaque compte. Leur présence ou leur SELECT * interne ne suffit pas à conclure à une matérialisation coûteuse : vérifier les plans MySQL avant de proposer leur remplacement.

## Vérifications exécutées

- PHPStan niveau 8 avec règles strictes : réussi, aucune erreur.
- `php tests/Build/Tooling/project-structure.php` : réussi, 255 déclarations PSR-4 et 390 imports.
- `php tests/Domain/Auth/admin-access.php` : réussi.
- `php tests/Assets/Css/page-styles.php` : échec confirmé, vue SQL manquante.

Suite complète, navigateur, schéma réel et profiling HTTP non exécutés. L'audit ne conclut donc pas que le projet entier passe ses tests.

## Ordre recommandé

1. Finir le retrait SQL et remettre les tests de styles au vert.
2. Retirer AdminMiddleware et trancher le maintien de rewardAll.
3. Mesurer la recherche, puis optimiser la révision des collections et les requêtes coûteuses.
4. Nettoyer les anciens bundles avec la rétention existante.
5. Réduire les duplications seulement quand un changement fonctionnel le rend utile.
