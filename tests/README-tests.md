# Tests LoliSSR

Ce dossier contient le système de tests automatisés du projet **LoliSSR**.

Les tests permettent de vérifier rapidement que les routes, redirections,
AJAX et fonctionnalités principales fonctionnent correctement.

---

# Structure actuelle

```text
tests/
├── cases/
│   ├── test-update.php
│   ├── test-ajax.php
│   ├── test-canonical.php
│   ├── test-pagination.php
│   ├── test-smoke.php
│
├── reports/
│   (rapports générés automatiquement)
│
├── bootstrap.php
├── config.php
├── run-tests.php
├── run-tests.bat
├── README-tests.md