@echo off

REM ==========================================
REM Lancer les tests PHP (double-clic friendly)
REM ==========================================

echo.
echo ===============================
echo        LANCEMENT TESTS
echo ===============================
echo.

REM Se placer dans le dossier du projet
cd /d %~dp0

REM Vérifie si PHP est accessible
php -v >nul 2>&1

if %errorlevel% neq 0 (
    echo ERREUR : PHP n'est pas dans le PATH.
    echo.
    echo Ajoute PHP au PATH Windows puis relance.
    pause
    exit /b
)

REM Lancer le script principal de tests
php run-tests.php

echo.
echo ===============================
echo        TESTS TERMINES
echo ===============================

pause