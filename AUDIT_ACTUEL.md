
## Code mort confirmé et faux positifs

| ID | Élément | Preuve | Nettoyage proposé |
|---|---|---|---|
| D1 | `App/Support/Helpers.php:69` — is_logged() | Seulement déclaration et garde function_exists dans le dépôt ; aucun appel trouvé | Retirer le bloc si aucune intégration externe ne l'utilise |
| D2 | `public/js/core/modal/alert-modal.js:5` — alertModal() | Déclaration et réexport dans modal.js ; aucun consommateur trouvé | Retirer fichier et réexport, puis reconstruire les assets |
| D3 | `public/js/core/modal/modal.js:18` — réexport titleModal | Le consommateur customization.js importe directement le module du profil | Retirer le réexport ; conserver title-modal.js |

Ces suppressions n'ont pas été appliquées dans cette demande d'analyse. Gain attendu : simplification, pas accélération spectaculaire ; le bundler peut déjà éliminer les exports inutilisés.

Faux positifs écartés :

- ErrorController est référencé par `public/index.php` comme callback de Bootstrap ; conserver la classe et les vues d'erreur.
- Les contrôleurs sont référencés par routes et callbacks : peu de références textuelles ne signifie pas qu'une méthode est morte.
- Les fonctions de debug sont également accessibles par chaînes et globals ; leur faible nombre de références ne justifie pas une suppression.
- Les anciens bundles/chunks sont soumis à une rétention pour les pages déjà ouvertes. Un ancien chunk SQL peut appartenir à un build retiré : il ne prouve pas qu'une console SQL reste exposée par le serveur. Le test HTTP vérifie la suppression de cette console. Utiliser js:prune selon la rétention existante.
- Les variantes grid, manifestes, scripts de maintenance et tests sont des ressources générées ou des points d'entrée explicites, pas du code mort du seul fait de l'absence d'appel depuis App.
