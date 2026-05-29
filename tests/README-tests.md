# LoliSSR HTTP Test Suite

Suite de tests HTTP légère pour LoliSSR.

## Objectifs

- Vérifier les routes publiques
- Vérifier les routes AJAX
- Vérifier les URLs canoniques
- Vérifier les codes HTTP
- Vérifier la présence de contenu HTML

## Garanties

- 100% safe
- 0 écriture BDD
- 0 suppression BDD
- 0 upload réel
- 0 mutation de données
- 0 SQL manuel
- 0 effet de bord

## Structure

```text
tests/
│
├── Http/
│   ├── cases/
│   │   └── safe/
│   │
│   ├── reports/
│   │
│   ├── Support/
│   │   ├── Assertions.php
│   │   ├── HtmlReport.php
│   │   ├── HttpClient.php
│   │   ├── Stats.php
│   │   └── Terminal.php
│   │
│   ├── bootstrap.php
│   ├── bootstrap-runner.php
│   └── config.php
│
├── run-tests.php
└── run-tests.bat
```

## Exécution

```bash
php tests/run-tests.php
```

ou

```bash
tests/run-tests.bat
```

## Rapport

Après exécution :

```text
tests/Http/reports/latest.html
```

contient le rapport complet.