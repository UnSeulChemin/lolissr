# Revue du projet — 4 octobre 2026

Revue des chemins principaux : repositories et requêtes, authentification et sessions, isolation des comptes, cache, uploads, assets, styles et navigation SPA. Aucun blocage supplémentaire identifié dans ce périmètre. Cette revue et les tests ne remplacent pas une mesure sous charge sur le serveur de production.

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
