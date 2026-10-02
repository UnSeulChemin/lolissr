# Audit de App

## Corrections

- Une grammaire disparue après le contrôle du contrôleur renvoie désormais 404
  depuis le service. Le test `collection-update-existence.php` vérifie aussi
  le rollback et la libération du verrou d'ordre.
- Les flashcards transmettent au maximum 50 cartes et un compteur, sans charger
  tous les identifiants. La navigation recharge les lots par position et conserve
  au maximum 50 cartes côté navigateur. Le total est actualisé à chaque lot et
  après une validation. Les anciens endpoints par identifiant restent disponibles
  pour les onglets déjà ouverts. Le gain porte sur la mémoire PHP/JavaScript et
  le volume HTML ; `COUNT(*)` et les offsets profonds gardent un coût SQL dépendant
  du volume. Aucun gain de latence SQL n'est revendiqué.
- Les recherches figurine, nendoroid et peluche partagent `SearchesCollectibles` :
  normalisation, filtres, projection, tri et limite de 20 résultats sont conservés.

Les régressions `flashcard-pages.php`, `collectible-search.php` et les tests HTTP
couvrent les lots et recherches. `flashcard-pages-browser.js` vérifie les limites
de lots, les deux sens de navigation, le bouclage, la suppression, les erreurs
réseau et l'annulation lors d'un changement de page.

- Les modifications d'artbooks verrouillent l'objet avant de lire sa source
  auteur/série, puis écrivent par identifiant. Le changement de statut de lecture
  verrouille également l'objet avant de décider des récompenses XP. Un objet
  absent renvoie 404 ; une modification inchangée reste un succès.
  `collection-update-existence.php` couvre aussi les artbooks, leurs deux types
  de source et leur statut de lecture sur une table temporaire MySQL.
- Les sept contrôleurs de modification conservent le code d'erreur du
  `ServiceResult` au lieu de remplacer systématiquement les échecs par 422.

- Les modifications de figurines, nendoroids et peluches vérifient l'existence
  sous verrou `SELECT ... FOR UPDATE` dans leur transaction, puis mettent à jour
  l'identifiant trouvé. Une disparition renvoie 404 ; une ligne inchangée reste
  un succès. Le contrôle partagé réside dans `UpdatesExistingCollection`.
  `collection-update-existence.php` couvre ces comportements sur des tables
  temporaires MySQL ainsi que le refus d'un appel hors transaction.

- Les cinq collections suppriment l'identifiant observé, avec contrôle du nombre
  de lignes supprimées. Un objet disparu renvoie 404 et ne déclenche aucun nettoyage
  d'image ; une recréation sous le même slug/numéro est préservée.
- Les nouveaux uploads portent un suffixe aléatoire propre au fichier. Le préfixe
  lisible est limité en octets pour accepter les noms multioctets. Le nettoyage
  différé d'un ancien objet ne réutilise pas le chemin d'une nouvelle image.
- Les extensions sont comparées au MIME détecté (avec l'alias jpeg/jpg) ; une
  discordance renvoie 422. Les formats sans correspondance connue sont refusés.
  Les images déjà stockées ne sont pas renommées.

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

`collection-delete-identity.php` reproduit un remplacement entre lecture et
suppression sur cinq tables SQLite en mémoire. `media-integrity.php` vérifie
les paires MIME/extensions et la préservation d'une nouvelle image lors du
nettoyage de l'ancienne ; seules la provenance HTTP et l'opération de déplacement
d'upload sont simulées, le contenu et les opérations disque sont réels.

Validation réussie : `composer validate --no-check-publish`,
`composer check-platform-reqs` et `composer check` (PHPStan, 67 tests HTTP, SPA
et toutes les régressions). Les versions des paquets du lock restent inchangées.
