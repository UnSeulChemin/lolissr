@echo off
title LoliSSR Tests

cls

echo.
echo ======================================
echo          LANCEMENT DES TESTS
echo ======================================
echo.

cd /d "%~dp0"

php -v >nul 2>&1

if errorlevel 1 (
    echo [ERREUR] PHP non detecte dans le PATH.
    echo.
    pause
    exit /b 1
)

if not exist "run-tests.php" (
    echo [ERREUR] run-tests.php introuvable.
    echo.
    pause
    exit /b 1
)

echo Lancement...
echo.

php run-tests.php

echo.
echo ======================================
echo            FIN DES TESTS
echo ======================================
echo.

pause