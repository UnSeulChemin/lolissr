@echo off
title LoliSSR - Tests UNITAIRES
color 0F

echo ================================
echo   LoliSSR - Tests UNITAIRES
echo ================================
echo.

REM Se placer dans le dossier racine du projet
cd /d "%~dp0\.."

echo Dossier courant :
cd
echo.

REM Verification PHPUnit
if not exist vendor\bin\phpunit.bat (
    echo [ERREUR] PHPUnit introuvable dans vendor\bin
    pause
    exit /b 1
)

echo Lancement des tests Unit...
echo.

call vendor\bin\phpunit.bat --testsuite Unit

echo.
echo ================================
echo   Fin des tests
echo ================================
pause