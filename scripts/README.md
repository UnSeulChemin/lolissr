# COMMANDES CLI

Les commandes publiques sont déclarées dans le [composer.json](../composer.json).




```text
=================================================
ORGANISATION
=================================================
```


= Où trouver les commandes

| Dossier | Rôle |
| --- | --- |
| `Assets/` | Construction CSS/JavaScript, images de profil et rétention des bundles |
| `Database/` | Sauvegarde de la base et analyse des index |
| `Maintenance/` | Nettoyage local et cache de bootstrap |
| `Profile/` | Audit et rattrapage des récompenses XP |
| `Release/` | Assemblage des livraisons et publication Git |
| `Support/` | Classes partagées par les commandes et leurs tests |

Les sources de commandes utilisent `verbe-objet.php`. Les classes partagées
utilisent `PascalCase.php`, avec un nom décrivant leur responsabilité.
Les empreintes de sources JavaScript dans `Assets/javascript-sources.php` sont
générées par le build.

Les scripts de maintenance, de base et de publication ont des effets propres :
la validation habituelle utilise `composer check` et `composer browser-tests`.
Le build des assets s'exécute avec `composer assets:build`.




```text
=================================================
COMMANDES COURANTES
=================================================
```


= Construire les assets

```sh
composer assets:build
```


= Vérifier le code et les parcours

```sh
composer check
composer browser-tests
```


= Générer une archive de livraison

```sh
composer release:zip
```

```text
Sources du projet
    │
    ▼
Build des assets
    │
    ▼
Préparation de la livraison
    │
    ▼
Archive ZIP dans releases/
```


= Nettoyer l'environnement local

```sh
composer dev:reset
```

Cette commande vide les logs, le cache et les sessions, puis régénère l'autoload.


= Sauvegarder la base

```sh
composer backup
```

La sauvegarde utilise les paramètres de connexion du `.env`.
