# APPLICATION LOLISSR

`App/` contient les fonctionnalités du site : authentification, collections
(manga, artbook, figurine, nendoroid, peluche), apprentissage du chinois et profil.
Les mécanismes HTTP et techniques sont décrits dans le
[README du framework](../Framework/README.md). Les options et les routes sont
documentées dans [Config/README.md](../Config/README.md).



<a id="organisation"></a>

```text
=================================================
ORGANISATION
=================================================
```


= Où ranger le code

```text
App/
│
├── Http/
│   ├── Controllers/     Recevoir la requête et choisir la réponse
│   ├── Requests/        Valider les formulaires
│   └── Routing/         Déclarer les routes partagées
│
├── DTO/
│   └── [Domaine]/
│       ├── Inputs/      Données reçues
│       └── Responses/   Données préparées pour l'affichage
│
├── Services/            Appliquer les règles métier
├── Repositories/        Exécuter les requêtes SQL
├── Models/              Représenter les données de la base
│
├── Views/               Afficher les pages et les fragments
├── Cache/               Mémoriser les résultats applicatifs
├── Support/             Partager les utilitaires
├── Constants/           Définir les barèmes et catalogues
├── Enums/               Nommer les valeurs métier possibles
└── Providers/           Enregistrer les dépendances
```


= Rôle de chaque dossier

| Dossier | Rôle |
| --- | --- |
| `Http/Controllers/` | Actions HTTP, validation des entrées, choix du rendu ou de la réponse JSON. |
| `Http/Requests/` | Règles des formulaires et création des DTO d'entrée. |
| `Http/Routing/` | Enregistrement des routes partagées des collections. |
| `DTO/` | Données d'entrée, résultats de lecture et résultats de service. |
| `Services/` | Cas d'usage, règles métier, transactions, uploads et récompenses XP. |
| `Repositories/` | Requêtes SQL par domaine, socle `AbstractRepository` et traits partagés. |
| `Models/` | Objets hydratés depuis la base, sans accès SQL. |
| `Views/` | Templates PHP, layouts, pages et erreurs. |
| `Cache/` | Clés et orchestration du cache applicatif, notamment le tableau de bord. |
| `Constants/`, `Enums/` | Barèmes, titres, récompenses et valeurs métier nommées. |
| `Providers/` | Enregistrement explicite des dépendances dans le conteneur. |
| `Support/` | Helpers et fonctions partagées propres au site. |

Les sous-dossiers par domaine sont conservés entre les couches, notamment
`Artbook`, distinct de `Manga`. Les DTO utilisent le suffixe `Data` ; les entrées
sont dans `Inputs/` et les données de présentation dans `Responses/`.
Les traits de services et de repositories sont placés dans `Concerns/`.
Les utilitaires sont regroupés dans `Support/Assets`, `Support/Media` et
`Support/Manga`. Voir l'[arborescence du projet](../docs/project-structure.md).



<a id="parcours-dune-requête"></a>

```text
=================================================
PARCOURS D'UNE REQUÊTE
=================================================
```


Les routes de [Config/routes.php](../Config/routes.php) et de `Config/routes/`
désignent les actions des contrôleurs. Le conteneur construit leurs dépendances.
Pour une écriture par formulaire, le parcours habituel est :

= Exemple : enregistrer un formulaire

```text
Navigateur
    │
    ▼
Route + middlewares
    │
    ▼
Contrôleur
    ├── FormRequest ──► DTO d'entrée
    │                       │
    │                       ▼
    │                    Service
    │                       │
    │                       ▼
    │                   Repository ◄──► Base de données
    │                       │
    ◄──── ServiceResult ◄───┘
    │
    ▼
Réponse JSON ou redirection
```

Pour une lecture, le service retourne les données nécessaires à la vue ou à la
réponse JSON. Le contrôleur commun gère le rendu HTML, les fragments SPA,
les messages de session et les réponses de service.

Exemple à suivre : [MangaController](Http/Controllers/Manga/MangaController.php),
[MangaCreateRequest](Http/Requests/Manga/MangaCreateRequest.php),
[MangaWriteService](Services/Manga/MangaWriteService.php) et
[MangaRepository](Repositories/Manga/MangaRepository.php).



<a id="conventions-à-préserver"></a>

```text
=================================================
CONVENTIONS À PRÉSERVER
=================================================
```


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
  de leurs middlewares ; `CollectionRouteRegistrar` partage les routes des trois collections.



<a id="ajouter-une-fonctionnalité"></a>

```text
=================================================
AJOUTER UNE FONCTIONNALITÉ
=================================================
```


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



<a id="vérification"></a>

```text
=================================================
VÉRIFICATION
=================================================
```


`composer check` exécute PHPStan, les tests HTTP/SPA et les régressions.
`composer browser-tests` couvre les interactions dans le navigateur.
Consulter le [guide des tests](../docs/guide.md) pour les prérequis et
l'isolation des données. Le [rapport d'audit App](../docs/app-audit.md)
décrit les corrections vérifiées ; il complète cette documentation d'architecture.
