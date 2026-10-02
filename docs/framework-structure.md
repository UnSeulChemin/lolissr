# Arborescence du framework

Les classes sont regroupées par responsabilité. Le namespace suit le chemin
du fichier sous `Framework/`, conformément à l'autoload PSR-4 de Composer.

```text
Framework/
├── Application/       Démarrage, noyau et cache de bootstrap
├── Auth/              Contrat d'authentification
├── Cache/             Cache applicatif et invalidation
├── Config/            Configuration, environnement et validation
├── Container/         Injection de dépendances et plans de paramètres
├── Database/          Connexion et transactions
├── Debug/             Profiler
├── Http/
│   ├── Exceptions/    Erreurs HTTP et interruption par réponse JSON
│   ├── Middleware/    Authentification, CSRF et en-têtes de sécurité
│   └── *.php          Requêtes, réponses, session et gestion des erreurs
├── Logging/           Journal applicatif
├── Routing/           Routes, index et dispatch
├── Security/          Politique de sécurité du contenu
├── Support/           Helpers, chaînes et dates
└── Validation/
    └── Concerns/      Règles de validation par type de donnée
```

La session appartient à `Http` car elle gère les sessions PHP et leurs cookies.
Les exceptions regroupées dans `Http/Exceptions` portent des statuts HTTP ou
une réponse JSON. `ContainerResolutionException` reste dans `Container`.
Le logger possède son propre module, utilisé aussi bien par HTTP que par les
transactions et le cache. `Support` conserve les utilitaires transversaux.

Cette réorganisation change les namespaces des classes déplacées, sans modifier
leur comportement. Les références de l'application et des tests sont mises à jour.
Lors d'un déploiement sur une installation existante, régénérer l'autoload avec
`composer dump-autoload` (ou via l'installation Composer habituelle).
