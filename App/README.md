# Application LoliSSR

`App/` contient les fonctionnalités du site : authentification, collections
(manga, artbook, figurine, nendoroid, peluche), apprentissage du chinois et profil.
Les mécanismes HTTP et techniques sont décrits dans le
[README du framework](../Framework/README.md). Les options et les routes sont
documentées dans [Config/README.md](../Config/README.md).

## Organisation

| Dossier | Rôle |
| --- | --- |
| `Controllers/` | Actions HTTP, validation des entrées, choix du rendu ou de la réponse JSON. |
| `Http/Requests/` | Règles des formulaires et création des DTO d'entrée. |
| `DTO/` | Données d'entrée, résultats de lecture et résultats de service. |
| `Services/` | Cas d'usage, règles métier, transactions, uploads et récompenses XP. |
| `Repositories/` | Requêtes SQL et accès aux données, par domaine. |
| `Models/` | Objets hydratés depuis la base et socle d'accès SQL partagé. |
| `Views/` | Templates PHP, layouts, pages et erreurs. |
| `Cache/` | Clés et orchestration du cache applicatif, notamment le tableau de bord. |
| `Constants/`, `Enums/` | Barèmes, titres, récompenses et valeurs métier nommées. |
| `Providers/` | Enregistrement explicite des dépendances dans le conteneur. |
| `Support/` | Helpers et fonctions partagées propres au site. |

Les sous-dossiers par domaine sont conservés entre les couches. `Exceptions/`
et `Migrations/` sont actuellement des emplacements réservés ; ils ne constituent
pas un système de migration opérationnel.

## Parcours d'une requête

Les routes de [Config/routes.php](../Config/routes.php) et de `Config/routes/`
désignent les actions des contrôleurs. Le conteneur construit leurs dépendances.
Pour une écriture par formulaire, le parcours habituel est :

```text
Route et middlewares → Contrôleur → FormRequest → DTO d'entrée
                                 → Service → Repository → Base de données
                                 ← ServiceResult → JSON ou redirection
```

Pour une lecture, le service retourne les données nécessaires à la vue ou à la
réponse JSON. Le contrôleur commun gère le rendu HTML, les fragments SPA,
les messages de session et les réponses de service.

Exemple à suivre : [MangaController](Controllers/Manga/MangaController.php),
[MangaCreateRequest](Http/Requests/Manga/MangaCreateRequest.php),
[MangaWriteService](Services/Manga/MangaWriteService.php) et
[MangaRepository](Repositories/Manga/MangaRepository.php).

## Conventions à préserver

- Valider le formulaire avant d'utiliser son DTO. Pour les actions JSON simples,
  utiliser les contrôles d'entrée adaptés et conserver la validation métier.
- Garder le SQL dans les repositories et utiliser les paramètres des requêtes.
  Les vues affichent les données ; elles ne lancent pas de requêtes.
- Regrouper les écritures dépendantes dans `Database::transaction()`.
  Un `ServiceResult` en échec provoque son annulation ; les transactions imbriquées
  ne sont pas prises en charge.
- Faire passer les récompenses XP par les services existants et
  `UserLevelService::addComputedXp()` : attribution et mise à jour du niveau
  partagent le verrou utilisateur et les protections contre les doublons.
- Invalider le cache concerné après une écriture réussie. Pour les créations avec
  image, réutiliser `CollectionCreationService`, qui nettoie l'upload en cas d'échec.
- Échapper le texte dynamique avec `e()` dans les vues. Le helper
  `returnPathInput()` limite les destinations `return_to` aux chemins internes
  relatifs à l'application ; une destination refusée utilise le repli du contrôleur.
- Déclarer explicitement les protections des routes : authentification, CSRF
  pour les écritures et contrainte JSON lorsque nécessaire. Les groupes héritent
  de leurs middlewares ; `CollectionRoutes` partage les routes des trois collections.

## Ajouter une fonctionnalité

1. Choisir le domaine et ajouter la route avec ses protections.
2. Définir les règles d'entrée et le DTO si l'action reçoit un formulaire.
3. Implémenter le service et les requêtes du repository, avec la transaction
   et l'invalidation nécessaires.
4. Ajouter la vue ou la réponse JSON. Déclarer les styles propres à la page dans
   `Config/styles.php` et reconstruire les assets si leurs sources changent.
5. Ajouter une régression sur le comportement métier ou HTTP concerné.

[ServiceProvider](Providers/ServiceProvider.php) relie notamment
`AuthenticationInterface` à `AuthService`. Une classe concrète dont le constructeur
est résoluble n'a pas besoin d'un enregistrement supplémentaire.

## Vérification

`composer check` exécute PHPStan, les tests HTTP/SPA et les régressions.
`composer browser-tests` couvre les interactions dans le navigateur.
Consulter le [guide des tests](../tests/Docs/guide.md) pour les prérequis et
l'isolation des données. Le [rapport d'audit App](../tests/Docs/app-audit.md)
décrit les corrections vérifiées ; il complète cette documentation d'architecture.
