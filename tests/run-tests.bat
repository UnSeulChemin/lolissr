@echo off
title LoliSSR - Tests HTTP
cls

cd /d "%~dp0"

echo.
echo ======================================
echo         LoliSSR - Tests HTTP
echo ======================================
echo.

php -v >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] PHP non detecte dans le PATH.
    echo.
    pause
    exit /b 1
)

if not exist "run-tests.php" (
    echo [ERREUR] run-tests.php introuvable dans le dossier tests.
    echo.
    pause
    exit /b 1
)

echo Lancement des tests HTTP...
echo.

php run-tests.php

echo.
echo ======================================
echo           Fin des tests HTTP
echo ======================================
echo.

pause