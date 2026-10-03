# FORMATAGE DX

Depuis la racine du projet :

```sh
php dx-format.php --check   # Liste les ecarts sans ecrire
php dx-format.php           # Applique le formatage
```

Les raccourcis Composer sont `composer dx:check` et `composer dx`.
`php dx-format.php --help` affiche les options.

## CONFIGURATION

[dx.json](../dx.json) definit les dossiers et fichiers a parcourir, les extensions,
les exclusions et la longueur de ligne. Les chemins sont relatifs a la racine du
projet. Une exclusion terminee par `/` exclut un dossier et ses descendants ; les
autres exclusions designent un fichier exact. Une configuration alternative peut
etre selectionnee avec `--config=chemin.json`.

Les dependances, `.git`, les donnees d'execution, les releases et les bundles
JavaScript sont toujours exclus. Les liens symboliques rencontres pendant le
parcours sont ignores. Les manifests PHP generes et le bundle CSS sont exclus de
la configuration par defaut.

## REGLES AUTOMATIQUES

- PHP : imports simples classes par bloc App, Framework puis autres imports,
  avec tri alphabetique dans chaque bloc ; les commentaires entre imports sont
  conserves et delimitent les blocs a trier.
- PHP et JavaScript : accolades de blocs en Allman, appels, signatures et tableaux
  courts sur une ligne lorsqu'ils restent lisibles ; virgules finales superflues
  retirees, espacement vertical excessif reduit et titres encadres par des
  separateurs `// ===` ou `// ---` convertis en majuscules.
- JavaScript : valeurs simples d'affectations et de proprietes compactees.
- CSS : valeurs courtes de declarations reunies sur une ligne.

Les expressions contenant des commentaires, des blocs ou des litteraux multiligne
restent sur plusieurs lignes. Les fins de ligne du fichier sont conservees.
Les sources PHP utilisant `__LINE__` ou `__halt_compiler` restent intactes.

Le regroupement des methodes par domaine metier, les exceptions a l'ordre
public/prive et le choix des titres de sections en majuscules restent des decisions
de refactoring manuel. Le script preserve l'organisation existante.

## VALIDATION

La syntaxe PHP et les instructions sont comparees avant et apres formatage.
Les chaines, templates HTML et contenus heredoc/nowdoc sont preserves. Pour
JavaScript et CSS, esbuild doit produire exactement le meme code minifie avant et
apres ; toute difference interrompt la commande avant les ecritures.

Le binaire esbuild du projet est requis lorsqu'une source JS/CSS doit etre modifiee.
Si necessaire, l'installer avec `composer js:install`. Aucun telechargement n'est
effectue automatiquement par le formateur.

Les transformations sont toutes calculees et validees avant d'ecrire. Chaque
fichier est ensuite remplace de facon atomique. Un fichier modifie entretemps est
refuse ; une erreur d'ecriture peut laisser les fichiers precedents deja formates.

Codes de sortie : `0` pour un formatage reussi ou un controle sans ecart,
`1` pour des ecarts avec `--check`, `2` pour une erreur.

Apres un formatage des sources, actualiser les empreintes et les assets :

```sh
composer assets:build
composer check
```

Le test du formateur se lance avec `php tests/Build/dx-format.php` ; il fait aussi
partie de `composer regression-tests`.
