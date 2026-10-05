
### 2. Dépendances PHP explicites

`scripts/Api/MangacollecClient.php` exige cURL et lève une exception si l'extension manque. `composer.json` ne déclare pas `ext-curl`. Un environnement peut donc satisfaire les dépendances Composer et échouer sur `manga:sync`. Déclarer cette exigence ou documenter explicitement le caractère optionnel de cette fonction. Pour les tests, vérifier aussi la disponibilité de SQLite, Edge et des exécutables utilisés.

### 3. Durcissement des connexions

`LoginThrottleService::identifierHash()` limite actuellement le couple compte/IP. Changer d'IP ouvre un autre compteur pour le même compte ; changer de compte ouvre un autre compteur pour la même IP. Pour un accès public plus large, prévoir des budgets supplémentaires par compte et par IP, avec une politique évitant le blocage abusif des utilisateurs.

`AuthService` accepte six caractères à l'inscription. Renforcer cette politique pour les nouveaux mots de passe si l'application ouvre davantage l'inscription. L'inscription est actuellement désactivée en production par les routes.

### 4. Limite du corps JSON

`Framework/Http/Request.php` lit entièrement `php://input` avant le décodage JSON. Il n'existe pas de limite applicative explicite à cet endroit. Une limite serveur peut déjà protéger l'application, mais elle n'a pas été vérifiée. Prévoir une limite en octets et une réponse 413 cohérente ; contrôler aussi la configuration du serveur.

### 5. Garde CLI des sauvegardes

`scripts/Database/backup-database.php` n'a pas la garde `PHP_SAPI !== 'cli'` présente dans plusieurs autres scripts. Si une configuration serveur rend ce chemin exécutable par HTTP, le script pourrait être déclenché hors console. Ajouter la garde et servir uniquement `public/` en production. Aucune exposition HTTP de ce script n'a été démontrée lors de cet audit.

### 6. Automatisation de validation et restauration

Aucune configuration CI YAML n'a été trouvée dans le dépôt examiné. Une CI peut lancer PHPStan et les tests adaptés à son environnement, avec MySQL et un navigateur disponibles. La commande locale complète existe déjà : `composer check:all`.

Le script de sauvegarde vérifie essentiellement l'existence et une taille minimale du dump, puis conserve au maximum vingt sauvegardes. Cela ne prouve pas la restaurabilité : ajouter un exercice périodique de restauration dans une base isolée et vérifier les données attendues. Ne pas restaurer dans la base active pour ce contrôle.

## Outils facultatifs

Une console unifiée faciliterait les arguments et messages des scripts, mais Composer couvre déjà leur lancement. ORM, queues, Redis, événements et moteur de templates dédié ne sont pas des prérequis manquants pour cette application. Les transactions imbriquées sont explicitement refusées : c'est une limite documentée du framework, à faire évoluer uniquement si un cas d'usage l'exige.

## Modifications pendant l'audit

Aucune modification du code applicatif ou du schéma. Création de ce rapport. Les tests utilisent leurs fixtures temporaires et génèrent leurs rapports habituels.
