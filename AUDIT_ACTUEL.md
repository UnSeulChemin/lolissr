
## Optimisations


### O2 — Recherche : optimiser le tri après mesure

Références : `App/Repositories/Collections/Concerns/SearchesCollectibles.php:27`, `App/Repositories/Manga/MangaSearchRepository.php:93`, `scripts/Tools/profile-substring-search.php`.

Les LIKE `%texte%` avec plusieurs branches OR ne deviennent pas des recherches substring indexées simplement en ajoutant un index sur le texte. Un index commençant par user_id et suivant l'ordre de tri peut néanmoins permettre d'obtenir rapidement les cinq premières correspondances fréquentes.

Profil local, deux requêtes et dix échantillons après chauffe : catégories individuelles environ 0,37 à 1,75 ms de médiane ; filtres de recommandations environ 1,48 à 1,56 ms. Le plan manga utilise un scan de 198 lignes avec filesort : ce n'est pas actuellement un goulet significatif.

Benchmark synthétique : projection réduite, LIMIT 5, index forcés, un compte de 20 000 lignes :

| Recherche | Index identité | Index utilisateur + tri | Interprétation |
|---|---:|---:|---|
| Fréquente (`series`) | 49,603 ms | 0,341 ms | Gain fort grâce à l'arrêt anticipé |
| Rare (`rare`) | 32,069 ms | 33,276 ms | Pas de gain dans ce scénario |
| Absente (`absent`) | 32,068 ms | 33,361 ms | Scan encore nécessaire |

L'index testé est `(user_id, origin, waifu, numero, id)`. Résultats locaux, synthétiques et non extrapolables tels quels à toutes les tables. Tester EXPLAIN et les temps sur des volumes représentatifs avant migration ; intégrer le coût disque et le coût des écritures. Ne pas remplacer la recherche par FULLTEXT sans valider ses différences fonctionnelles.

### O3 — Mutualiser les jobs administratifs

Références : `App/Services/Admin/MaintenanceJob.php`, `ReleaseJob.php`, `RecommendationJob.php`.

Lecture d'état verrouillée, sortie bornée, états queued/running, lancement Windows/Unix et gestion d'échec sont largement dupliqués. Extraire un composant commun réduirait les corrections à appliquer trois fois. Les classes métiers peuvent garder leurs commandes autorisées et leurs paramètres.

Les tests de concurrence et de lancement existants sont utiles et doivent rester. Cette optimisation réduit surtout le coût de maintenance ; aucun gain de latence utilisateur n'est revendiqué.

### O4 — Surveiller la croissance des métadonnées de cache

Référence : `Framework/Cache/Cache.php:147`.

clear() retire les valeurs et avance les générations, mais conserve les verrous stables et les versions. Ce comportement est nécessaire pour ne pas créer deux verrous concurrents sur des fichiers distincts. En revanche, les clés disparues peuvent laisser des métadonnées sur disque, avec un coût de scandir et d'invalidation croissant.

Action proposée : mesurer nombre de clés/métadonnées et durée de purge. Si un compactage devient nécessaire, l'effectuer avec les producteurs arrêtés ou sous un protocole global coordonné. Ne pas supprimer simplement les `.lock` pendant le fonctionnement normal.

Autre observation : remember() attend jusqu'à deux secondes puis recalcule hors verrou. Cela borne l'attente mais peut amplifier la charge lors de calculs lents. Les tests de contention passent ; changer cette politique seulement après mesure des compteurs cache.lock_timeout/cache.recompute et décision sur la fraîcheur acceptable.

## Code mort confirmé et faux positifs

| ID | Élément | Preuve | Nettoyage proposé |
|---|---|---|---|
| D1 | `App/Support/Helpers.php:69` — is_logged() | Seulement déclaration et garde function_exists dans le dépôt ; aucun appel trouvé | Retirer le bloc si aucune intégration externe ne l'utilise |
| D2 | `public/js/core/modal/alert-modal.js:5` — alertModal() | Déclaration et réexport dans modal.js ; aucun consommateur trouvé | Retirer fichier et réexport, puis reconstruire les assets |
| D3 | `public/js/core/modal/modal.js:18` — réexport titleModal | Le consommateur customization.js importe directement le module du profil | Retirer le réexport ; conserver title-modal.js |

Ces suppressions n'ont pas été appliquées dans cette demande d'analyse. Gain attendu : simplification, pas accélération spectaculaire ; le bundler peut déjà éliminer les exports inutilisés.

Faux positifs écartés :

- ErrorController est référencé par `public/index.php` comme callback de Bootstrap ; conserver la classe et les vues d'erreur.
- Les contrôleurs sont référencés par routes et callbacks : peu de références textuelles ne signifie pas qu'une méthode est morte.
- Les fonctions de debug sont également accessibles par chaînes et globals ; leur faible nombre de références ne justifie pas une suppression.
- Les anciens bundles/chunks sont soumis à une rétention pour les pages déjà ouvertes. Un ancien chunk SQL peut appartenir à un build retiré : il ne prouve pas qu'une console SQL reste exposée par le serveur. Le test HTTP vérifie la suppression de cette console. Utiliser js:prune selon la rétention existante.
- Les variantes grid, manifestes, scripts de maintenance et tests sont des ressources générées ou des points d'entrée explicites, pas du code mort du seul fait de l'absence d'appel depuis App.

## Vérifications effectuées

| Contrôle | Résultat |
|---|---|
| PHPStan niveau 8 + règles strictes | Aucune erreur |
| Syntaxe PHP | 479 fichiers vérifiés, aucune erreur |
| Composer validate | Valide |
| composer regression-tests | Succès, y compris concurrence cache, migrations, ownership, uploads, XP et builds |
| composer http-tests | 106/106 cas HTTP ; contrôles SPA/recherche/flashcards et limite JSON également réussis |
| composer browser-tests | Toutes les suites réussies après relance avec les permissions nécessaires à Edge |
| HTTP ownership avec deux sessions | Succès : lectures/écritures/suppressions étrangères refusées, CSRF et caches séparés |
| Schéma MySQL | Succès sur fixtures temporaires |
| Index redondants | 0 index éligible à une suppression |
| Dépendances de production | Succès ; vendor de développement conservé |
| Portabilité Apache | Succès pour racine et sous-répertoire renommé |
| Cache HTTP assets | Succès : immutable pour bundles, 304 cohérents, erreurs non mises en cache immutable |
| Profil de recherche | Mesures et plans SQL obtenus ; aucun changement des données/indexes applicatifs |

La première tentative navigateur a échoué avant exécution des scénarios à cause du démarrage GPU/permissions d'Edge dans l'environnement restreint. La relance a exécuté toutes les suites avec succès.

Les 106 cas HTTP ont une moyenne locale de 54,91 ms sur un seul passage. Ce chiffre inclut le transport local, ne constitue pas une médiane de production et ne représente pas une mesure sous charge.

## Ordre de travail recommandé

1. Borner les transferts de couvertures et de JSON pendant la réception.
2. Unifier la résolution d'identité de connexion et ajouter la vérification du hash factice, en conservant les tests de collation/throttling.
3. Appliquer le nettoyage D1–D3 et sécuriser l'API textuelle des modales.
4. Ajouter les garde-fous de suppression des images et clarifier la politique des commandes dev en production.
5. Mutualiser les jobs ; n'ajouter des index de recherche et un compactage de cache qu'après mesures représentatives.
