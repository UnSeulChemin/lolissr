# Revue du projet — 4 octobre 2026

Nouvelle analyse : les défauts relevés ci-dessous ont ensuite été corrigés à la demande de l'utilisateur : vues 403/422 créées, notes facultatives ajoutées sous le statut du formulaire manga et enregistrées avec un total cohérent, référence au README absent retirée. Aucun README applicatif n'était présent à supprimer. Les sections suivantes conservent les constats de l'audit avant correction. Cette revue et les tests ne remplacent pas une mesure sous charge sur le serveur de production.

## Contre-vérification complète du 4 octobre 2026

Audit demandé du code applicatif, framework, base locale, assets et contrôles disponibles. Aucun changement fonctionnel ni migration appliqués dans cette contre-vérification. Les tests MySQL utilisent des tables temporaires ou des fixtures dédiées ; les requêtes supplémentaires sur les données historiques ont été exécutées en transaction de lecture seule.

### Défaut confirmé : erreurs HTML 403 et 422

`App/Http/Controllers/ErrorController.php` déclare `errors/403` et `errors/422`, alors que `App/Views/errors/` contient seulement 404, 405, 419 et 500. L'appel direct à `ErrorController::handle()` pour chacun de ces statuts reproduit `Vue introuvable`. `Controller::ensureViewExists()` lance une exception, puis `Framework/Http/ErrorHandler::handleFailure()` répond 500 avec `Critical framework error.`

Impact : un refus 403 rendu en HTML perd son statut et sa page attendus. Pour 422, le gestionnaire de validation redirige habituellement les formulaires ; le défaut concerne les chemins qui atteignent effectivement le renderer HTML. Les erreurs JSON utilisent une autre branche, sans ces vues.

Correction à réaliser : fournir les deux vues, ou définir un rendu générique conservant le statut et son titre. Ajouter une régression sur ces deux rendus. Les tests actuels passent malgré ce défaut : ils ne couvrent pas ces vues.

### Cohérence fonctionnelle à décider : notes des nouveaux mangas

`MangaWriteService::createManga()` attribue explicitement 1/5 à la jaquette, 1/5 au livre et 2/10 au total. Le formulaire de création ne demande pas ces notes. Le schéma SQL accepte maintenant des notes absentes et ses valeurs par défaut sont NULL.

Les contraintes SQL sont respectées, mais un manga non évalué entre donc dans les statistiques comme évalué à 2/10. Si cette note initiale est voulue, documenter ce choix ; sinon utiliser NULL à la création et vérifier les affichages et filtres associés. Aucune ancienne note ne doit être convertie automatiquement, car son intention est inconnue.

### Base réelle et requêtes

- 11 tables, 657 lignes au total : 197 mangas, 7 artbooks, 8 figurines, 17 nendoroids, 3 peluches, 372 grammaires, 21 mots, 2 comptes, 17 récompenses de succès, 13 récompenses de séries, 0 tentative de connexion.
- Aucun propriétaire orphelin dans les sept domaines privés et les deux historiques de récompenses.
- Aucun doublon `(user_id, slug, numero)` dans les cinq collections ; aucune incohérence entre les composantes des notes manga et leur total.
- `db:check` valide les contraintes sur le schéma réel. `users:check` valide les accès, sessions, recherches, cache et récompenses entre deux comptes.
- L'audit dédié des index ne propose aucune suppression parmi ses cinq paires candidates. Il ne démontre pas l'absence de toute redondance possible.
- EXPLAIN sur la liste de vocabulaire : index `idx_chinois_vocabulaire_user_langue_maitrise_id`, accès `ref`, pas de filesort. La sous-requête de propriété est fusionnée dans ce plan ; son `SELECT *` ne démontre pas à lui seul une matérialisation de toutes les colonnes.
- EXPLAIN sur une recherche manga contenant `%a%` : parcours inverse de l'index primaire et filtrage. Une pagination bornée ne garantit pas que peu de lignes seront examinées pour une recherche rare. À mesurer sur une collection volumineuse avant de changer la recherche.

### Code mort, assets et documentation

Recherche des déclarations et références PHP dans les sources, configuration, point d'entrée, scripts et tests : aucune classe ni méthode inutilisée confirmée par ce contrôle. Les candidats initiaux `ErrorController`, `ServiceProvider`, `Bootstrap` et les méthodes d'audit XP sont effectivement utilisés au démarrage ou par les scripts. Ce contrôle textuel ne prouve pas que toutes les branches sont atteignables.

246 fichiers présents dans `public/js/dist`, environ 848 Kio ; le bundle actif est vérifié par les tests. Les anciens fichiers sont conservés pour les pages encore ouvertes et supprimables après le délai de sept jours depuis leur observation par le serveur. Éviter une suppression manuelle immédiate ; utiliser le mécanisme `js:prune` prévu lors de la maintenance.

`.env.example` renvoie vers `Config/README.md`, absent du dépôt : référence documentaire à corriger. `notes.txt` conserve également deux idées de roadmap ; leur réalisation ne fait pas partie des fonctionnalités testées.

### Résultat des contrôles relancés

- `composer check` : réussi, PHPStan niveau 8 et règles strictes sans erreur, 67/67 contrôles HTTP, suites SPA et toutes les régressions déclarées.
- `composer db:check` et `composer users:check` : réussis.
- `composer dx:check` : 584 sources, aucun écart.
- Navigateur Edge hors sandbox : bundle de production et neuf scénarios exécutés, 49 contrôles réussis. Le lanceur `composer browser-tests` est revenu avec un code zéro mais une sortie incomplète sans son marqueur final ; les neuf scénarios ont donc été relancés individuellement et validés. La fiabilité du compte rendu du lanceur sous Windows reste à examiner. La tentative sandbox échoue sans DOM exploitable.
- `git diff --check` : réussi après la rédaction du rapport.

Conclusion : base cohérente et parcours principaux validés localement ; corriger les vues d'erreur manquantes et décider du comportement initial des notes avant de parler de finition complète. Aucune grosse optimisation indispensable démontrée à ce volume. Les mesures sous charge, la restauration effective des sauvegardes et la configuration du serveur de production restent hors des validations effectuées. Aucun audit ne garantit une absence absolue de bugs.

## Corrections appliquées

- Les cartes 404, 405, 419 et 500 utilisaient le fond gris `--color-surface-detail` des fiches. Le composant partagé impose maintenant `--color-surface`, blanc comme la carte SQL. La spécificité du sélecteur conserve ce blanc lorsque les styles de détail sont chargés ensuite.
- Bundle CSS et versions des assets régénérés.
- Conventions de présentation réappliquées aux 11 fichiers signalés par `dx:check`, sans changement fonctionnel.

## Optimisations déjà présentes

- Projections SQL adaptées aux listes ; recherches limitées à 20 résultats et collections paginées.
- Flashcards chargées par lots de 50, avec parcours par curseur et comptage cohérent avec le contenu.
- Agrégations des statistiques et attributions XP regroupées ; tests contre les doubles récompenses et les écritures concurrentes.
- Cache du tableau de bord séparé par utilisateur, invalidé après les mutations concernées ; libération du verrou de session pendant le traitement des routes.
- Styles spécifiques chargés selon la page, bundles de production, modules JavaScript différés, préchargement et réponses SPA allégées.
- Validation des uploads, noms de fichiers distincts, optimisation et variantes des miniatures.

## Pistes à mesurer si le volume augmente

- Les recherches contenant `LIKE '%texte%'` nécessitent un examen des lignes de l'utilisateur ; un index classique sur le texte ne suffit pas. Envisager une recherche dédiée uniquement si les mesures le justifient, en conservant les correspondances actuelles et le comportement chinois/pinyin.
- Les listes avec `OFFSET` et les agrégations par série peuvent coûter davantage sur de grosses collections. Comparer leurs plans et temps SQL sur des volumes représentatifs avant d'ajouter des index ou de remplacer la pagination.
- Le cache fichier convient à cette installation. Une installation sur plusieurs serveurs demanderait un stockage partagé et une invalidation commune.

Pas de migration SQL ni de renommage supplémentaire nécessaire pour la correction des pages d'erreur.

## Validation

- `composer check` : analyse PHP, 67 contrôles HTTP, réponses SPA et régressions réussis après les changements.
- `composer browser-tests` : réussi avec Edge hors sandbox ; la première tentative dans le sandbox n'a produit aucun DOM.
- Vérification ponctuelle dans Edge : fond calculé de `.error-card.detail-card` égal à `rgb(255, 255, 255)` après chargement de `detail.css`.
- `composer db:check` et `composer users:check` : schéma MySQL, comptes, sessions, cache, XP et succès indépendants validés avec fixtures dédiées et nettoyées.
- `composer dx:check` : 584 sources contrôlées, aucun fichier à reformater.
- `git diff --check` : aucun défaut de whitespace.
