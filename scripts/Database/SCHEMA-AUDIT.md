# Audit de la BDD — 4 octobre 2026

**Evolution ulterieure appliquee :** les donnees sont maintenant propres a chaque utilisateur. Ce document decrit l'audit initial ; voir [l'isolation des comptes](USER-OWNERSHIP.md) et la migration `2026-10-04-user-ownership.sql` pour le fonctionnement actuel.

Comparaison du code, de l'export `127_0_0_1.sql` et du schema MySQL 9.1 reel : 11 tables, 657 lignes. La base locale a ete migree apres sauvegarde, sans suppression de donnees ni changement du total des XP utilisateurs.

## Incoherences corrigees

| Constat | Correction |
| --- | --- |
| `MangaRepository` ecrit NULL pour l'editeur et les notes absentes, alors que le schema impose NOT NULL et des notes par defaut 1/1/2. | Colonnes nullable, valeurs par defaut NULL. Les anciennes notes restent conservees : impossible de savoir si elles etaient intentionnelles. |
| `note` est une somme stockee que SQL peut desynchroniser. | CHECK : notes entre 1 et 5, somme exacte, NULL si une composante manque. |
| `manga_series_rewards` utilise une collation differente de `manga.slug`. Une jointure directe echoue avec l'erreur 1267. | Collations alignees sur utf8mb4_0900_ai_ci. |
| Le journal des series n'est consulte nulle part dans le code, malgre 13 entrees historiques ; 10 series ont des indicateurs manquants. | Journal utilise lors de l'attribution ; indicateurs historiques synchronises sans attribuer d'XP. Suppression/recreation d'une serie ne redonne plus sa recompense de completion. |
| `achievement_xp_rewards.user_id` est signe alors que `users.id` est non signe ; aucun lien referentiel. | INT UNSIGNED et FK vers users, avec RESTRICT pour conserver l'historique. Aucun utilisateur orphelin detecte avant migration. |
| Identifiants manga/chinois et numeros manga/artbook signes. | INT UNSIGNED uniformise. CHECK sur numeros positifs. |
| TINYINT(1) ne limite pas un indicateur a 0 ou 1. | CHECK sur lecture, possession, maitrise et recompenses. Aucun indicateur invalide detecte. Une recompense peut rester a 1 apres une bascule a 0 : comportement volontaire. |
| Extensions de profil par defaut PNG en SQL et WebP dans le code. | Defaults WebP, enum pour frame_extension comme avatar/banner. |
| Grammaire : created_at accepte NULL, contrairement aux autres contenus. | NOT NULL en conservant TIMESTAMP et les dates existantes. Aucun NULL detecte. |
| Index `idx_slug` nendoroid/peluche dupliquent le prefixe de leur index unique (slug, numero). | Suppression des deux index redondants. |
| Vocabulaire filtre par langue puis trie par maitrise ASC, id DESC ; index langue seul. | Index composite (langue, maitrise ASC, id DESC). EXPLAIN passe de parcours complet + filesort a ref + Using index sur les donnees presentes. |
| Noms generiques d'index (`username`, `idx_maitrise`, etc.). | Noms explicites uq_users_username et idx_<table>_<colonnes>. |
| Hash de connexion SHA-256 stocke avec une collation linguistique utf8mb4. | CHAR(64) ASCII avec comparaison binaire. |
| Validation hauteur de figurine sans plafond, stockage DECIMAL(4,1) limite a 999.9. | Plafond 999.9 dans les deux formulaires PHP. |

Le CHECK existant `chk_artbook_source` (exactement auteur OU serie) est conserve. L'export joint ne restitue pas correctement toute la definition de cette table : la base reelle fait reference.

## Conventions conservees et limites

Les tables metier au singulier et les tables techniques au pluriel suivent deux conventions. Le schema melange francais et anglais (`livre`, `waifu`, `company`, `collect`). Les index sont uniformises maintenant ; renommer tous les champs demanderait une migration coordonnee des modeles, DTO, formulaires, JS et requetes. Les commentaires SQL explicitent les notes sans changer les noms publics. `jacquette` est une faute historique (orthographe : jaquette), actuellement conservee pour compatibilite.

Lors de cet audit initial, les collections et progressions etaient globales. L'evolution suivante a retenu des contenus entierement personnels : user_id est obligatoire sur les tables metier, les donnees historiques sont attribuees au compte principal et tous les acces sont filtres. Voir USER-OWNERSHIP.md.

`created_at` melange DATETIME pour les contenus et TIMESTAMP pour certains journaux. Pas de conversion automatique : TIMESTAMP depend du fuseau de session. Les longueurs des slugs different selon les domaines et respectent leurs validations ; elles ne sont pas reduites. Les index de recherche existants sont conserves sauf redondance demontree. Les petites tables ne justifient pas des index sur tous les champs.

La synchronisation des indicateurs historiques peut corriger les statistiques d'XP de collection affichees ; elle ne modifie ni `users.xp` ni `users.level`. Les recompenses par tome restent attachees aux lignes : leur suppression/recreation est un sujet distinct du journal de completion de serie.

## Fichiers et verification

- Sauvegarde complete avant migration : `storage/backups/database/backup-2026-10-04_18-32-27.sql` (contient des donnees privees, ne pas publier).
- SQL livre : `scripts/Database/migrations/2026-10-04-schema-consistency.sql`.
- Migration deja appliquee localement ; SQL a executer UNE FOIS sur une autre base ayant le schema initial, apres controles des donnees et sauvegarde. DDL MySQL non annulable par un simple ROLLBACK ; restauration via la sauvegarde.
- `composer db:check` verifie le schema reel avec des fixtures temporaires.
- `php tests/Domain/manga-xp-batch.php` verifie attribution unique, rollback, historique ancien et suppression/recreation.

Resultats apres migration : `composer db:check`, `composer phpstan`, toutes les regressions et les tests HTTP/SPA passent (67/67 controles HTTP). Les 657 lignes sont conservees. L'historique des 13 series rejoint maintenant les tomes sans erreur de collation ; aucun indicateur historique manquant.

Documentation MySQL : [cles etrangeres](https://dev.mysql.com/doc/refman/8.0/en/create-table-foreign-keys.html), [commits implicites du DDL](https://dev.mysql.com/doc/refman/8.4/en/implicit-commit.html).
