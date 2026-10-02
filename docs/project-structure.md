# Structure et conventions du projet

## Responsabilités

L'application conserve une organisation par couches, chacune regroupée par
domaine. Un formulaire suit `Http/Controllers` → `Http/Requests` → `DTO/Inputs`
→ `Services` → `Repositories`. Les services de lecture préparent des objets
`DTO/Responses` affichés par `Views`.

`Models` contient les objets hydratés ; ils n'exécutent pas de SQL. Le socle SQL
commun est `App/Repositories/AbstractRepository.php`, avec les traits de
construction et d'exécution dans `Repositories/Concerns`.

Les artbooks possèdent un dossier `Artbook` dans les contrôleurs, requêtes, DTO,
services, repositories et vues. Leur regroupement dans le menu manga et leurs
URL publiques restent des décisions de navigation, distinctes de l'organisation
du code.

`Services/Media` valide et stocke les images. `Services/Collections` coordonne
leur création avec les transactions et le nettoyage en cas d'échec.
`Services/Home/DashboardStatsService` calcule les statistiques de l'accueil.
`Services/Sql/SqlExecutionService` représente explicitement la console SQL,
qui peut exécuter des lectures comme des écritures.

## Framework

`Framework/Config/ApplicationConfig` expose les options typées du site, au même
endroit que `DatabaseConfig` et `UploadConfig`. `Application/HttpKernel` gère
le cycle HTTP ; `Container/ContainerRegistry` conserve le conteneur actif.
Le bootstrap, le routage, la validation, les sessions et les autres mécanismes
gardent leurs modules par responsabilité.

## Frontend

Les modules applicatifs sont regroupés par domaine dans `public/js/`. Les
initialiseurs de routes vivent dans `router/initializers`, l'historique dans
`router/history` et le démarrage global dans `boot`. Les modales spécifiques
au profil sont dans `profile/modals` ; les dialogues communs restent dans
`core/modal`.

Les styles communs sont dans `base`, les composants dans `components`, les
utilitaires dans `utilities` et les styles par page dans `pages`.
`Config/styles.php` associe les noms des vues à leurs dépendances CSS.

Les vues utilisent les actions techniques `create`, `edit`, `show`, `index`,
et des noms spécifiques tels que `achievements`, `customization`, `unread` ou
`links`. Un fragment de liste est `partials/items.php`, indépendamment du
protocole HTTP utilisé pour le demander.

## Scripts, tests et documentation

`scripts/Support` héberge les classes de build partagées. Les commandes restent
dans `Assets`, `Database`, `Maintenance`, `Profile` et `Release`.
`tests/Support/bootstrap.php` est commun aux régressions et à PHPStan.
`tests/Browser/run-browser-scenario.php` exécute un scénario exportant
`runBrowserScenario`. Les guides et audits sont centralisés dans `docs`.

## Compatibilité et déploiement

Les déplacements concernent les chemins internes, les namespaces et les noms
techniques. Les URL métier, colonnes SQL, variables d'environnement et formats
des réponses restent compatibles. Les noms français correspondant au métier
ou aux données ne sont pas traduits implicitement.

La [table de correspondance](structure-renames.json) indique les anciens et
nouveaux fichiers. Lors d'une mise à jour d'une installation existante :

1. Installer le code et régénérer l'autoload Composer.
2. Construire les assets avec `composer assets:build`.
3. Vider l'ancien cache de bootstrap avec `composer bootstrap:clear`, puis le
   reconstruire sur l'hôte cible si utilisé en production.
4. Recharger le service PHP si son OPcache ne vérifie pas les modifications.

Les anciens bundles JavaScript restent soumis au délai de rétention existant
pour les onglets ouverts. Les fichiers de `vendor`, les images, les sessions,
les sauvegardes et les notes personnelles ne font pas partie des renommages.

Cette réorganisation facilite la recherche et la maintenance du code ; elle ne
revendique pas de gain de temps d'exécution.
