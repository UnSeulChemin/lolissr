# FICHIERS WEB

`index.php` démarre l'application. Les URL publiques sont déclarées dans
[Config/routes.php](../Config/routes.php).



<a id="organisation"></a>

```text
=================================================
ORGANISATION
=================================================
```


= Sources et fichiers publiés

| Chemin | Contenu |
| --- | --- |
| `js/app.js` | Entrée JavaScript source |
| `js/boot/` | Démarrage et initialiseurs globaux |
| `js/core/` | HTTP, DOM, erreurs, configuration et dialogues communs |
| `js/router/` | Navigation SPA, historique, cache et initialiseurs de routes |
| `js/profile/`, `js/manga/`, etc. | Modules propres aux fonctionnalités |
| `js/dist/` | Bundles générés et versions encore conservées pour les anciens onglets |
| `css/base/` | Fondations des styles |
| `css/components/` | Composants partagés, dont les modales |
| `css/pages/` | Styles par fonctionnalité |
| `css/utilities/`, `css/partials/` | Utilitaires et éléments de layout |
| `images/` | Images du site et fichiers locaux téléversés |



<a id="construction-des-assets"></a>

```text
=================================================
CONSTRUCTION DES ASSETS
=================================================
```


= Des sources aux fichiers chargés par le navigateur

```text
Sources JavaScript                 Sources CSS
js/app.js + modules                css/app.css + imports
         │                                  │
         └──────────────┬───────────────────┘
                        ▼
              composer assets:build
                        │
          ┌─────────────┴──────────────┐
          ▼                            ▼
     js/dist/                  css/app.bundle.css
          │                            │
          └─────────────┬──────────────┘
                        ▼
             Manifestes et versions
```

En local, les pages utilisent les sources. En production, elles utilisent les
bundles construits.


= Reconstruire après une modification

```sh
composer assets:build
```

Cette commande
actualise le CSS compilé, les bundles JavaScript et les manifestes.
Les noms `app-<empreinte>.js` permettent de distinguer les versions du build ;
ils ne sont pas renommés manuellement.



<a id="conventions"></a>

```text
=================================================
CONVENTIONS
=================================================
```


= Noms des fichiers

Les fichiers JS/CSS sont nommés en `kebab-case`. Les pages JS utilisent `create`
et `edit` pour leurs formulaires. `profile` est le nom du domaine technique ;
les URL métier `/profil` et les chemins d'images historiques restent conservés.
