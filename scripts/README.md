# Scripts du projet

Les commandes sont définies dans `composer.json`. Leurs noms restent stables
quand les fichiers sont réorganisés.

| Dossier | Responsabilité |
| --- | --- |
| `Assets` | Compilation et préparation des assets |
| `Database` | Sauvegardes, migrations SQL et audit des index |
| `Maintenance` | Nettoyage, cache de démarrage et réinitialisation |
| `Manga` | Synchronisation Mangacollec et planification Windows |
| `Profile` | Audit et rattrapage des récompenses XP |
| `Release` | Archives de production et publication Git |
| `Tools` | Diagnostic, formatage, analyse statique et mesures |
| `Support` | Écriture atomique et verrou de build partagés |

## Assets

`Assets/build-assets.php` orchestre la compilation CSS et JavaScript, puis
actualise les versions des assets.

- `Assets/Css` : compilation du bundle CSS.
- `Assets/JavaScript` : installation d'esbuild, compilation, métadonnées des
  sources (`javascript-sources.php`) et nettoyage des anciens bundles.
- `Assets/Images` : images de profil, manifeste, variantes de grille et
  optimisation des miniatures.

Chaque groupe possède un dossier `Support` pour ses classes auxiliaires.
Ces classes ne sont pas des commandes à exécuter directement. Garder une classe
par fichier et réserver `scripts/Support` aux utilitaires partagés entre outils.

Les données d'exécution vont dans `storage`, les archives dans `releases` et les
assets publiés dans `public`. Les tests se trouvent principalement dans
`tests/Build` et `tests/Assets`. Conserver les noms et le contenu des migrations
déjà appliquées dans `Database/migrations` : le runner vérifie leur intégrité.
