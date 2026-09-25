# CSS par page

`public/css/app.css` contient le socle commun : variables, mise en page, cartes,
formulaires, en-tête, recherche et composants génériques.

Les dépendances propres aux pages sont déclarées dans `Config/styles.php`.
Les chemins de vues sont relatifs à `App/Views`, sans `.php`. Un chemin terminé
par `/` couvre tout son dossier. L'ordre des fichiers détermine leur cascade CSS.

Exemple pour une nouvelle page :

```php
'page/exemple.css' => ['pages/exemple/index'],
```

Le contrôleur transmet cette sélection au layout HTML et aux réponses JSON.
Le layout génère les `<link data-page-style>` dès le premier chargement.
Pendant la navigation, `page-styles.js` charge les fichiers manquants sans les
activer, puis les active au remplacement du contenu et retire les anciens.
Les styles partagés sont réutilisés. Les navigations annulées nettoient leurs
propres liens. Une erreur ou un délai supérieur à 10 secondes déclenche le
traitement d'erreur existant du routeur (rechargement classique par défaut).

Les fragments de pagination héritent des styles de leur page parente : ajouter
les dépendances à celle-ci si un nouveau fragment en nécessite.

## Vérifications indépendantes de la suite HTTP

```powershell
php tests/page-styles.php
php tests/run-page-styles-browser.php
```

Le second test nécessite Apache local et Microsoft Edge. Il utilise une page
HTML temporaire, un profil Edge temporaire et aucune connexion à un compte.
Passer l'URL en premier argument si le projet n'est pas servi sous `http://localhost/lolissr`.

Après mise à jour, faire Ctrl+F5 pour renouveler le CSS et les modules JavaScript.
Vérifier les passages accueil → manga → profil → chinois → SQL, le retour arrière
et les clics rapides. Les règles visuelles des feuilles existantes sont conservées.

## Composants réutilisables

Les styles partagés résident dans `public/css/components/` et restent chargés à
la demande via le manifeste :

- `detail.css` : fiches manga, artbook, figurine, nendoroid et peluche.
- `status-toggle.css` : bouton `.status-toggle` et icône `.status-toggle-icon` ;
  ajouter `.status-toggle-icon--read` pour le contour du marque-page. États
  `.active`, `:hover` et `:disabled`. Les classes `js-*` existantes restent en place.
- `profile-avatar.css` : image d'avatar et cadre superposé communs aux deux pages du profil.
- `media-picker.css` : grilles et boutons de choix d'avatar, cadre ou bannière.
  Utiliser `.media-picker-grid`, `.media-picker-item` et la variante
  `.media-picker-item--avatar` ou `.media-picker-item--banner`.
- `summary.css` : sections et cartes de synthèse partagées entre accueil, profil,
  SQL et listes manga. Les sélecteurs HTML existants sont conservés.

Les anciens fichiers déplacés sont de petits points d'entrée de compatibilité.
Le manifeste utilise directement les nouveaux composants : ne pas ajouter les
anciens chemins en plus. Les styles propres à une seule page restent dans `page/`.

`css-equivalence-browser.js` compare les propriétés calculées de composants
avant/après à 420, 768 et 1440 pixels, avec leurs pseudo-éléments et leurs états.
Le lanceur accepte en deuxième argument ce module et en troisième un fichier JSON
de comparaison contenant `before` et `after` (chemin CSS → contenu), et `cases`
(`name`, listes de chemins `before` et `after`). Le snapshot doit être pris avant
la refactorisation ; ce contrôle ne remplace pas une revue visuelle des pages réelles.
