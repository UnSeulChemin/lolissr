
## 1. Priorité haute avant production : chemins Apache liés au dossier local

Constat statique confirmé : `.htaccess:2` contient `RewriteBase /lolissr/`, sa ligne 4 exclut seulement les URI commençant par `/lolissr/public/`, et `public/.htaccess:2` contient `RewriteBase /lolissr/public/`.

La procédure PRODUCTION.txt propose aussi une installation à la racine du domaine avec APP_BASE_URI=/, mais cette option PHP ne change pas les règles Apache. Le build de livraison inclut le .htaccess racine sans adaptation de ces chemins.

Risque : dans une installation à la racine ou sous un autre nom de dossier, redirections internes vers le mauvais chemin et/ou réécritures répétées vers public/. Le résultat dépend aussi du DocumentRoot et des règles Apache de l'hébergeur. Le site local sous /lolissr passe les tests ; le déploiement à la racine n'a pas été testé pendant cet audit.

Action : rendre les règles indépendantes du dossier ou générer les bons chemins de livraison ; vérifier en HTTP une installation à la racine et une installation sous un autre sous-dossier. À traiter avant d'utiliser la procédure à la racine.

## 2. Priorité moyenne : invalidations manga plus larges que nécessaire

Le trigger UPDATE de `scripts/Database/migrations/2026-10-07-manga-collection-revisions.sql` change la révision après chaque UPDATE, même pour lecture, notes, commentaire ou indicateurs XP. Or la collection utilisée par les recommandations lit uniquement slug, livre et numero, avec user_id pour son périmètre.

Conséquence : une simple note ou récompense XP peut invalider recommandations, favoris et filtres. Les changements de ces champs ne nécessitent pas de reconstruire leurs résultats. Pour un propriétaire inchangé, le trigger fournit aussi deux lignes avec le même user_id et actualise deux fois la même révision.

Action : une nouvelle migration pourrait limiter l'invalidation aux changements de user_id/slug/livre/numero, comparer les titres exactement pour conserver les changements d'orthographe/casse, et ne modifier qu'une fois la ligne lorsque le compte ne change pas. Préserver INSERT/DELETE, transferts entre comptes, concurrence et rollback. Ne pas modifier la migration déjà appliquée.

Il s'agit d'une optimisation de coûts d'écriture et de taux de cache, pas d'une erreur de résultats. Le gain doit être mesuré avant de la prioriser.

## 3. Priorité basse : branches et validation dupliquées dans la maintenance

`scripts/Admin/run-maintenance.php:9` et `:11` répètent l'affectation et la validation de ownerId. Les bras xp-check et xp-apply du match se répètent également aux lignes 27–30.

Les seconds bras ont les mêmes conditions que les premiers et ne sont jamais sélectionnés. Retirer le second bloc de validation et les deux bras supplémentaires. Les tests de maintenance actuels passent ; PHPStan analyse App et Framework, pas ce script.

## 4. Priorité basse : double lecture dans le repli avant migration

`MangaRecommendationService::collectionRevision()` calcule une empreinte de releaseCollection lorsque la table de révisions n'existe pas. En cas de cache invalide, le callback de all/favorites/searchFilters appelle ensuite releaseCollection une seconde fois.

Ce chemin conserve la justesse sur une installation sans migration, mais ajoute une lecture complète à froid par rapport à l'ancienne implémentation. Avec la migration appliquée, la lecture à chaud est une simple révision indexée et ce problème ne se produit pas.

Action éventuelle : réutiliser le snapshot chargé par le repli pendant l'opération courante, sans conserver une collection périmée entre appels. Faible priorité si toutes les installations appliquent normalement les migrations.

## 5. Robustesse des états des commandes admin

RecommendationJob, ReleaseJob et MaintenanceJob écrivent leur état JSON avec file_put_contents et LOCK_EX ; status lit ces fichiers sans verrou partagé. L'écriture n'est pas un remplacement atomique : un lecteur concurrent peut observer un fichier incomplet et répondre idle.

Les runners gardent un verrou de commande distinct, ce qui limite les doubles exécutions ; aucune double exécution démontrée ici. Le risque identifié concerne principalement le statut visible. Préférer un remplacement atomique du JSON, ou une lecture sous le même protocole de verrouillage. Une reproduction concurrente serait nécessaire avant de qualifier ce point de bug fonctionnel confirmé.

## Optimisations à conserver

- Pagination, projections ciblées et agrégats regroupés.
- Isolation par compte dans ownedTable ; les plans observés ne justifient pas son retrait.
- Révision manga indexée, empreintes persistantes des images, versions d'assets.
- Imports JavaScript différés, ordre d'initialisation et nettoyage de la navigation.
- Debounce, cache de recherche borné, annulations et invalidations frontend.
- Compilation configuration/routes et connexion DB singleton.
- Sauvegarde SQL incluant déjà routines, triggers et events.
- Livraison séparant les dépendances de production et préservant vendor de développement.

L'étude des sous-chaînes reste disponible dans RECHERCHE_PERFORMANCE.md : tous les domaines ont des index commençant par user_id. Les mesures ne justifient pas un remplacement de LIKE par FULLTEXT/préfixe.

## Vérifications exécutées

- Composer validate et PHPStan niveau 8/règles strictes : réussis.
- 106/106 tests HTTP : réussis ; moyenne locale du passage 55,08 ms, mesure unique hors benchmark de production.
- Tests HTTP SPA, limites JSON et totalité des scripts de régression Composer : réussis.
- Suite navigateur complète : réussie hors sandbox. Le premier essai dans la sandbox a échoué au démarrage GPU d'Edge, avant les assertions.
- Contrôle du schéma MySQL, absence d'index redondants, isolation des sessions/comptes HTTP : réussis.
- Dépendances de production et politique HTTP de cache des assets : réussis.
- Formatage : 641 sources analysées, aucun écart.

`composer check:all` s'est arrêté au lancement d'Edge dans l'environnement restreint ; la suite navigateur a été relancée hors sandbox et les contrôles suivants de check:all ont été exécutés séparément avec succès. Ce n'est pas une exécution unique de check:all entièrement réussie.

Pas de revue exhaustive de chaque ligne, pas de audit de dépendances/vulnérabilités externe, pas de profilage de charge simultanée, ni de couverture CSS navigateur exhaustive. Les conclusions portent sur les sources inspectées, références et contrôles ci-dessus. Les modifications locales préexistantes et celles faites depuis l'IDE ont été conservées.
