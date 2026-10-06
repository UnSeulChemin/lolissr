

## Outils facultatifs

Une console unifiée faciliterait les arguments et messages des scripts, mais Composer couvre déjà leur lancement. ORM, queues, Redis, événements et moteur de templates dédié ne sont pas des prérequis manquants pour cette application. Les transactions imbriquées sont explicitement refusées : c'est une limite documentée du framework, à faire évoluer uniquement si un cas d'usage l'exige.

## Modifications pendant l'audit

Aucune modification du code applicatif ou du schéma. Création de ce rapport. Les tests utilisent leurs fixtures temporaires et génèrent leurs rapports habituels.
