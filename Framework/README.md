# FRAMEWORK DE LOLISSR

`Framework/` fournit les mécanismes techniques partagés du site. Les règles
métier et les templates résident dans [App/](../App/README.md), les paramètres
et les déclarations de routes dans [Config/](../Config/README.md).



<a id="organisation"></a>

```text
=================================================
ORGANISATION
=================================================
```


= Les mécanismes partagés

```text
Framework/
│
├── Application/    Démarrage → bootstrap → noyau HTTP
├── Config/         Environnement et options typées
├── Container/      Construction et partage des objets
├── Routing/        Association URL → action du contrôleur
│
├── Http/           Requêtes, réponses, sessions et middlewares
├── Auth/           Contrat d'authentification
├── Security/       Politique de sécurité du contenu
├── Validation/     Règles de validation réutilisables
│
├── Database/       Connexion et transactions
├── Cache/          Cache sur fichiers
├── Logging/        Journaux
├── Debug/          Mesures de performance
└── Support/        Fonctions techniques partagées
```


= Rôle de chaque dossier

| Dossier | Rôle |
| --- | --- |
| `Application/` | Bootstrap, `HttpKernel` et cache compilé. |
| `Config/` | Environnement, validation, résolution et accès typé aux options (`ApplicationConfig`, `DatabaseConfig`, `UploadConfig`). |
| `Container/` | Construction des objets et partage des instances enregistrées. |
| `Routing/` | Déclaration, sélection des routes et invocation des actions. |
| `Http/` | Requête, réponse, session, formulaires, erreurs et middlewares. |
| `Auth/` | Contrat d'authentification implémenté par l'application. |
| `Database/` | Connexion PDO et gestion des transactions. |
| `Cache/` | Cache sur fichiers et coordination des accès concurrents. |
| `Logging/`, `Debug/` | Journaux et mesures du profiler. |
| `Security/` | Mécanismes de sécurité, dont la politique CSP. |
| `Validation/` | Validation des entrées et règles réutilisables. |
| `Support/` | Helpers techniques, chaînes et dates. |



<a id="démarrage-http"></a>

```text
=================================================
DÉMARRAGE HTTP
=================================================
```


= De l'entrée du site à la réponse

```text
public/index.php
    │
    ▼
Bootstrap
    ├── Environnement + configuration
    ├── Conteneur + services
    └── Routes
          │
          ▼
      HttpKernel
          │
          ▼
       Routeur → Middlewares → Contrôleur → Réponse HTTP
```


= Les étapes du démarrage
[public/index.php](../public/index.php) charge l'autoload et les helpers, puis
appelle [Bootstrap::run()](Application/Bootstrap.php) avec le gestionnaire
d'erreurs et le fournisseur de services de l'application.

1. Le bootstrap charge `.env` et vide la configuration en mémoire.

2. Il vérifie le cache compilé. En cas d'absence ou d'invalidation, il valide
   l'environnement et utilise le chargement normal.

3. Il configure le fuseau horaire, les erreurs et le profiler, puis crée le
   conteneur et enregistre les services de l'application.

4. Il prépare le routeur depuis le cache ou les fichiers de routes.

5. [HttpKernel](Application/HttpKernel.php) ouvre la session, applique les en-têtes
   de sécurité et lance le dispatch de la requête.

Un échec précoce de configuration produit une réponse HTTP 500 générique et un
journal PHP. `Bootstrap::loadEnvOnly()` permet aux scripts de charger et valider
l'environnement sans démarrer le noyau HTTP.



<a id="configuration-et-cache-de-bootstrap"></a>

```text
=================================================
CONFIGURATION ET CACHE DE BOOTSTRAP
=================================================
```


`config('fichier.cle', $default)` charge les tableaux de `Config/` à la demande
et mémorise les résolutions. Un fichier ou une clé absents utilisent le défaut
de l'appel ; une valeur explicitement nulle reste nulle. Un fichier existant
retournant autre chose qu'un tableau est refusé.

`Config::clear()` vide la mémoire ; `Config::prime()` l'initialise depuis les
tableaux compilés. Ces opérations sont distinctes du cache applicatif.

`composer bootstrap:cache` construit la configuration et les routes sur le serveur
cible. Reconstruire cet artefact après modification du code, des routes, de la
configuration ou des assets. `composer bootstrap:clear` le désactive. Les variables
suivies et le validateur sont vérifiés au chargement ; les sources des routes et
des tableaux ne sont pas automatiquement surveillées. Voir le
[contrat de déploiement](../Config/README.md#cache-du-bootstrap-au-déploiement).



<a id="conteneur-et-routage"></a>

```text
=================================================
CONTENEUR ET ROUTAGE
=================================================
```


[Container](Container/Container.php) résout les dépendances des constructeurs.
Utiliser les enregistrements explicites pour les interfaces, les fabriques et les
instances partagées. Le bootstrap partage notamment la requête et la connexion
à la base ; le fournisseur de l'application configure l'authentification.

Le routeur accepte des groupes, préfixes, middlewares et paramètres tels que
`{numero:int}`. L'ordre des déclarations fait partie du comportement : les index
de routes doivent préserver cette priorité. Les méthodes non autorisées et les
routes absentes suivent des traitements distincts (405 et 404).

Les middlewares sont exécutés avant l'action. Une action sous forme de closure
ne peut pas être sérialisée dans le cache compilé ; les closures de groupes sont
exécutées lors de sa construction.



<a id="sessions-transactions-et-cache-applicatif"></a>

```text
=================================================
SESSIONS, TRANSACTIONS ET CACHE APPLICATIF
=================================================
```


- `Session::close()` libère le verrou. Utiliser `Session::withLock()` pour une
  séquence indivisible de lectures et d'écritures ; les accès après fermeture
  peuvent rouvrir la session et relire ses données.

- `Database::transaction()` valide le résultat normal, annule sur exception ou
  résultat implémentant `TransactionResult` et refusant le commit. Un simple
  retour `false` n'est pas ce contrat. Les transactions imbriquées sont refusées.
  `onRollback()` permet de restaurer un état mémoire lié à la transaction gérée.

- Le mode `testing` impose des transactions SQL en lecture seule sur les
  connexions créées normalement par `Database` ; il ne choisit pas une autre base.

- `Cache::remember()` calcule une valeur absente et peut mémoriser `null`.
  `Cache::forget()` l'invalide. Sous contention, un appel peut recalculer sans
  publier : ne pas supposer que le callback ne sera exécuté qu'une seule fois.
  L'expiration est plafonnée à `PHP_INT_MAX` pour les TTL extrêmes.

Les données d'exécution résident dans `storage/` : sessions, cache, journaux et
artefact de bootstrap ont des usages et des commandes de nettoyage distincts.



<a id="erreurs-et-mesures"></a>

```text
=================================================
ERREURS ET MESURES
=================================================
```


`ErrorHandler` centralise le traitement des erreurs ; l'application fournit leur
rendu. `Logger` écrit les journaux applicatifs et masque les clés sensibles qu'il
connaît : éviter d'inclure des secrets dans les messages libres.
Le profiler HTTP démarre lorsque debug et profiler sont activés. Ses mesures
servent à vérifier les coûts avant de modifier les chemins d'exécution.



<a id="modifier-et-vérifier"></a>

```text
=================================================
MODIFIER ET VÉRIFIER
=================================================
```


Conserver ici les mécanismes génériques, et dans `App/` les politiques métier.
Une modification de routage, de verrouillage, de session ou de transaction doit
préserver les contrats ci-dessus et être accompagnée d'une régression ciblée.

`composer check` regroupe PHPStan, les tests HTTP/SPA et les régressions.
Voir le [guide des tests](../docs/guide.md) pour leur exécution et le
[rapport d'audit du framework](../docs/framework-audit.md) pour l'historique
des corrections et les mesures existantes.
