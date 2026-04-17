@echo off
title LoliSSR Tests

echo.
echo ======================================
echo          LANCEMENT DES TESTS
echo ======================================
echo.

REM Se placer dans le dossier du .bat
cd /d "%~dp0"

REM Vérifier PHP
php -v >nul 2>&1

if errorlevel 1 (
    echo ERREUR : PHP n'est pas disponible dans le PATH.
    echo.
    pause
    exit /b 1
)

REM Vérifier fichier test
if not exist "run-tests.php" (
    echo ERREUR : run-tests.php introuvable.
    echo.
    pause
    exit /b 1
)

REM Lancer les tests
php run-tests.php

echo.
echo ======================================
echo            FIN DES TESTS
echo ======================================
echo.

pause