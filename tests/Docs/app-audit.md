# Audit de App

## Corrections

- Les formulaires de vocabulaire et de grammaire acceptent uniquement un chemin
  de retour relatif à l'application. Les URL externes, chemins absolus, traversées
  de répertoire, antislashs et caractères de contrôle utilisent la destination
  par défaut. La même règle s'applique à l'affichage du formulaire et à son envoi.
- Les listes vides conservent une première page accessible ; les pages suivantes
  sont introuvables pour manga, artbook, figurine, nendoroid, peluche et vocabulaire.
- La méthode `UserLevelService::addXp()`, sans appel dans le dépôt, est supprimée.
  Les récompenses continuent à utiliser `addComputedXp()`.
- Composer déclare `ext-intl` pour les identifiants de sections HSK et `ext-gd`
  pour la génération des images de profil. Le contrôle du support WebP sans perte
  dans le script de génération reste nécessaire.

## Arborescence

Les contrôleurs, requêtes, services, repositories, DTO et vues sont déjà groupés
par domaine. Aucun déplacement n'est nécessaire pour ces corrections.
`Config/` conserve les options et manifestes utilisés par l'application ; les
empreintes JavaScript réservées au build restent dans `scripts/Assets/`.

## Vérification

`tests/Domain/app-boundaries.php` vérifie les chemins de retour GET/POST et les
six paginations vides avec SQLite en mémoire. Il fait partie de `composer check`.

Validation réussie : `composer validate --no-check-publish`,
`composer check-platform-reqs` et `composer check` (PHPStan, 67 tests HTTP, SPA
et toutes les régressions). Les versions des paquets du lock restent inchangées.
