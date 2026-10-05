


### 5. Garde CLI des sauvegardes

`scripts/Database/backup-database.php` n'a pas la garde `PHP_SAPI !== 'cli'` présente dans plusieurs autres scripts. Si une configuration serveur rend ce chemin exécutable par HTTP, le script pourrait être déclenché hors console. Ajouter la garde et servir uniquement `public/` en production. Aucune exposition HTTP de ce script n'a été démontrée lors de cet audit.

### 6. Automatisation de validation et restauration

Aucune configuration CI YAML n'a été trouvée dans le dépôt examiné. Une CI peut lancer PHPStan et les tests adaptés à son environnement, avec MySQL et un navigateur disponibles. La commande locale complète existe déjà : `composer check:all`.

Le script de sauvegarde vérifie essentiellement l'existence et une taille minimale du dump, puis conserve au maximum vingt sauvegardes. Cela ne prouve pas la restaurabilité : ajouter un exercice périodique de restauration dans une base isolée et vérifier les données attendues. Ne pas restaurer dans la base active pour ce contrôle.

## Outils facultatifs

Une console unifiée faciliterait les arguments et messages des scripts, mais Composer couvre déjà leur lancement. ORM, queues, Redis, événements et moteur de templates dédié ne sont pas des prérequis manquants pour cette application. Les transactions imbriquées sont explicitement refusées : c'est une limite documentée du framework, à faire évoluer uniquement si un cas d'usage l'exige.

## Modifications pendant l'audit

Aucune modification du code applicatif ou du schéma. Création de ce rapport. Les tests utilisent leurs fixtures temporaires et génèrent leurs rapports habituels.
