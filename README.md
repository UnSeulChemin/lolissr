# LOLISSR

Application PHP pour les collections, l'apprentissage du chinois et le profil.
Le site utilise un framework local, des templates PHP et une navigation JavaScript.




```text
=================================================
ARBORESCENCE
=================================================
```


```text
App/                    Fonctionnalités et règles métier
  Http/                 Contrôleurs, formulaires et routes partagées
  DTO/                  Données d'entrée et de réponse, par domaine
  Services/             Cas d'usage, transactions et récompenses
  Repositories/         SQL et socle AbstractRepository
  Models/               Objets représentant les lignes de la base
  Views/                Pages, fragments, layouts et erreurs
  Support/              Utilitaires Assets, Media et Manga
  Cache/                Cache applicatif
  Constants/            Barèmes et catalogues
  Enums/                Valeurs métier typées
  Providers/            Enregistrement des dépendances
Framework/              Infrastructure HTTP, configuration, SQL et conteneur
Config/                 Options, routes et manifestes des assets
public/                 Point d'entrée HTTP, sources CSS/JS, bundles et images
scripts/                Commandes CLI par responsabilité ; utilitaires dans Support/
tests/                  Suites Framework, Domain, Assets, Build, Http et Browser
docs/                   Architecture, guides et audits
storage/                Caches, sessions, journaux, outils et sauvegardes locales
releases/               Archives générées
vendor/                 Dépendances Composer
```

Les domaines `Artbook`, `Manga`, `Figurine`, `Nendoroid`, `Peluche`, `Chinois`,
`Profile` et `Auth` utilisent les mêmes noms dans les couches PHP concernées.




```text
=================================================
CONVENTIONS
=================================================
```


- PHP : fichier et classe en `PascalCase`, namespace conforme au chemin PSR-4.

- DTO : suffixe `Data`, entrées dans `Inputs/`, résultats de présentation dans
  `Responses/`. `ServiceResult` représente le résultat d'une opération métier.

- Traits : dossier `Concerns/`, nom décrivant leur capacité.

- Vues : `index`, `create`, `edit`, `show` ; fragments dans `partials/`.

- JavaScript et CSS : noms en `kebab-case`, dossiers techniques en anglais.
  Les noms des domaines existants restent communs aux fonctionnalités.

- Tests et scripts : noms décrivant l'objet testé ou l'action exécutée.

- Les fichiers de `public/js/dist/` et les manifestes générés sont produits par
  le build. Le suffixe des bundles est une empreinte de contenu.




```text
=================================================
COMMANDES
=================================================
```


= Installer les dépendances

```sh
composer install
```


= Construire les fichiers CSS et JavaScript

```sh
composer assets:build
```


= Vérifier le code et les tests

```sh
composer check
```


= Vérifier les interactions dans le navigateur

```sh
composer browser-tests
```

Les tests HTTP et navigateur nécessitent Apache local ; certains tests métier
nécessitent MySQL. Voir les [prérequis de test](docs/guide.md).




```text
=================================================
DOCUMENTATION
=================================================
```


- [Architecture et conventions détaillées](docs/project-structure.md)

- [Application](App/README.md), [framework](Framework/README.md), [configuration](Config/README.md)

- [Tests](tests/README.md) et [commandes CLI](scripts/README.md)

- [Sources frontend et fichiers générés](public/README.md)

- [Correspondance des fichiers renommés](docs/structure-renames.json)
