# Corrections et optimisations du framework

## Relecture complémentaire — 1er octobre 2026

Relecture du bootstrap, du cache compilé, du conteneur, du routage, du cache
applicatif, des transactions et de la requête HTTP.

- `RouteCollection::candidates()` conserve directement l'ordre du groupe
  dynamique quand il est seul. Le tri reste nécessaire lorsque les routes à
  premier segment fixe et celles à premier segment dynamique se mélangent.
  La normalisation du chemin n'est plus calculée deux fois dans cet appel.
- La régression de routage compare toujours les résultats à un parcours
  linéaire et couvre désormais aussi une méthode sans groupe générique,
  avec routes dynamiques avant et après une route statique.
- La recherche textuelle des noms de méthodes publiques dans `App`,
  `Framework`, `Config`, `scripts` et `tests` ne révèle aucune méthode dont
  le nom apparaît uniquement à sa déclaration, hors constructeurs. Ce filtre
  ne prouve pas que tous les appels sont accessibles ; aucune suppression de
  code mort n'est justifiée par cette vérification seule.
- Le cache compilé exige toujours une reconstruction après modification des
  sources de configuration ou des routes : c'est son contrat actuel, pas une
  invalidation automatique à chaque requête.

Validation : `composer check` réussi (PHPStan, 67 tests HTTP, SPA et
régressions), puis test de routage relancé après extension des cas.
Le gain de cette micro-optimisation n'a pas été isolé par une mesure avant/après
et aucun gain sur le temps total des pages n'est revendiqué.

## Corrections précédentes

- Les valeurs `.env` non citées `true`, `false`, `null`, `empty` et leurs
  variantes entre parenthèses sont converties comme les variables système.
  Les guillemets préservent une chaîne littérale : `VALUE="false"` reste une
  chaîne. Les espaces des valeurs système ordinaires et des mots de passe
  cités sont conservés. Les erreurs indiquent le numéro physique de ligne.
- Un échec de configuration renvoie une réponse générique HTTP 500 et écrit
  les détails dans le journal PHP, avant que les services applicatifs existent.
- Le profiler mesure depuis l'entrée dans `Bootstrap::run()` et expose
  `bootstrap.configure`. Le temps d'autoload antérieur à cet appel est exclu.
- Le cache de bootstrap est validé à la compilation. Au chargement, une
  empreinte des variables consultées (y compris absentes) et du validateur
  permet d'éviter une nouvelle validation complète. `.env` reste relu pour
  détecter ses changements. Le format passe à la version 2 : les anciens
  artefacts sont ignorés, avec repli sur le démarrage normal. Reconstruire
  avec `composer bootstrap:cache` sur la machine cible pour utiliser ce cache.
- Les sessions réutilisent leur configuration lors des réouvertures. Les
  changements de nom, dossier, HTTPS ou proxy déclenchent sa reconfiguration.
  Les options de sécurité sont réappliquées à chaque ouverture ; les données
  sont toujours relues sous verrou pour conserver les mises à jour concurrentes.
- Les routes dynamiques sont indexées par méthode et premier segment statique.
  Les routes dont le premier segment est dynamique restent candidates. L'ordre
  des déclarations, des méthodes `Allow` et les priorités restent inchangés.
- `ParameterPlan` partage la lecture des paramètres entre routeur et conteneur.
  Les valeurs par défaut sont évaluées à chaque invocation, même avec un plan
  mis en cache. Les politiques propres à chaque appelant sont conservées.
- Composer déclare les extensions du framework ; les versions des paquets
  verrouillés restent inchangées. Aucun code utilisé par les tests n'est supprimé.

## Vérification et mesures

`composer check` exécute PHPStan, les tests HTTP/SPA et les régressions, avec
les nouveaux tests `tests/Framework/configuration.php` et `routing.php`.

Mesures CLI locales indicatives, sans extrapolation au temps d'une page :

| Scénario | Parcours normal | Parcours optimisé |
| --- | --- | --- |
| Recherche Allow, 208 routes synthétiques, 2 000 itérations | 0,1197 ms | 0,0172 ms |
| Configuration et routes, 200 itérations à chaud | 6,281 ms | 4,316 ms |

Reproduction :

```sh
php tests/Framework/routing.php --benchmark
php tests/Framework/bootstrap-cache.php --benchmark
```

La seconde mesure compare le chemin normal au cache compilé actuel ; elle
n'isole pas le gain des seules modifications de cet audit.
