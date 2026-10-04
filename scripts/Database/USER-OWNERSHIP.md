# Comptes et donnees personnelles — 4 octobre 2026

La migration `2026-10-04-user-ownership.sql` est deja appliquee a la BDD locale, apres l'audit initial. Chaque compte possede maintenant ses contenus, ses etats de lecture/collection/maitrise, son historique de recompenses et ses statistiques.

## Conservation des donnees

Les 657 lignes historiques sont conservees. Les contenus et le journal de series existants ont ete rattaches au compte historique `users.id = 1`. Les comptes existants conservent leurs niveaux, XP et recompenses de succes ; ces valeurs ne permettent pas de reconstruire des collections personnelles qui n'existaient pas auparavant. Le compte `test_runner` ne reprend donc pas la collection du compte principal.

La comparaison des empreintes de toutes les lignes avant/apres migration et apres nettoyage des tests confirme que les contenus, niveaux, XP et dates historiques sont inchanges. Seuls les nouveaux champs de proprietaire/administrateur ont ete ajoutes. Les identifiants AUTO_INCREMENT peuvent presenter des trous apres les tests jetables, comme apres toute suppression ; ils ne sont pas renumerotes.

Sauvegarde complete precedente : `storage/backups/database/backup-2026-10-04_18-45-09.sql`. Cette sauvegarde correspond a la base apres l'audit initial, avant l'isolation des comptes. Une restauration doit etre coordonnee avec la version du code correspondante.

## Fonctionnement

- Un nouveau compte commence au niveau 1, avec 0 XP et aucun contenu. Les succes eventuellement lies au niveau initial suivent les regles existantes.
- Les mangas, artbooks, figurines, nendoroids, peluches, grammaires et vocabulaires sont personnels. Les cours de chinois historiques ne sont pas copies automatiquement aux nouveaux comptes.
- Les lectures, possessions, maitrises, notes, commentaires et indicateurs de recompense appartiennent au proprietaire du contenu.
- Chaque table de contenu porte `user_id`, obligatoire, sans valeur par defaut, avec une FK vers users. Les indexes commencent par user_id lorsque les recherches sont propres au compte.
- L'unicite est `(user_id, slug, numero)` pour les collections : deux comptes peuvent utiliser le meme slug et numero. Le journal des series utilise `(user_id, slug)`.
- Les lectures, listes, recherches globales, compteurs, menus de grammaire et flashcards sont filtres par le compte courant. Les ecritures ne peuvent pas changer de proprietaire ou viser un enregistrement etranger. Les identifiants etrangers retournent 404 sur les routes metier.
- Les recompenses de lecture/collection et les recompenses de completion de serie sont independantes entre comptes. Le journal de completion reste conserve apres suppression/recreation d'une serie du meme compte.
- Le cache de l'accueil utilise une cle par utilisateur avec un nouveau namespace ; une modification de A n'invalide pas les donnees de B. La console SQL administrative invalide les caches des comptes existants.
- Les catalogues de personnalisation restent communs, mais les conditions de deblocage utilisent les statistiques personnelles et le niveau personnel.
- Les sessions et tokens CSRF restent attaches au navigateur. Deux profils de navigateur ou une fenetre privee permettent deux connexions distinctes. Deux onglets d'un meme profil partagent leur connexion.

## Administration et compatibilite

`users.is_admin` est faux par defaut ; seul le compte historique id=1 a ete marque administrateur. La console SQL, quand elle est active en environnement local, est reservee a un administrateur. Un compte ordinaire recoit 404 pour ses routes HTML et JSON. La console reste desactivee en production comme auparavant. Les inscriptions ne donnent jamais de droits administrateur. Le parametre `REGISTRATION_ENABLED` existant est conserve.

Le modele Manga accepte maintenant un editeur NULL, comme le schema et les formulaires ; l'affichage utilise une chaine vide lorsqu'il manque. Les modeles de collection declarent user_id pour eviter les proprietes dynamiques lors de l'hydratation PDO.

Les tests HTTP ont egalement expose un probleme de reponse CSRF : Apache envoyait 500 pour le code personnalise 419 sans libelle de statut. La reponse envoie maintenant explicitement 419 avec son libelle, y compris dans le rendu d'erreur de secours. Voir la [documentation PHP sur les codes personnalises selon le SAPI](https://bugs.php.net/bug.php?id=75009).

Les lectures SQL ordinaires utilisent des sources explicitement filtrees, que MySQL peut fusionner dans le plan indexe. Les lectures FOR UPDATE conservent leur filtre et leur verrou dans la requete sur la table de base : un verrou exterieur ne suffit pas pour une sous-requete. Voir les [verrous sur tables derivees](https://bugs.mysql.com/bug.php?id=90693).

## Verification et livraison

- `composer check` : PHPStan, HTTP/SPA et regressions.
- `composer db:check` : contraintes du schema reel et unicite personnelle, avec fixtures temporaires.
- `composer users:check` : test de deux comptes sur une base aleatoire creee puis supprimee, suivi d'un test de deux sessions HTTP avec comptes/contenus jetables nettoyes dans finally. Necessite MySQL local, Apache local et la permission de creer une base de test.
- Les tests couvrent les sept domaines, les recherches, la lecture et les ecritures interdites, les recompenses independantes, le journal de series personnel, l'inscription, le cache, les sessions, les tokens CSRF et la console SQL.

SQL a livrer : `scripts/Database/migrations/2026-10-04-user-ownership.sql`. Ne pas le rejouer sur la base locale deja migree. Sur une autre base, appliquer d'abord la migration de coherence du schema, sauvegarder, verifier l'identifiant du proprietaire historique et deployer le code avec cette migration. Le DDL MySQL effectue des commits implicites ; un simple ROLLBACK ne restaure pas le schema.
