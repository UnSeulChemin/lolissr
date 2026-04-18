@echo off
title LoliSSR - Coverage PHPUnit
cls

cd /d "%~dp0\.."

echo.
echo ================================
echo   LoliSSR - Coverage PHPUnit
echo ================================
echo.

php -v >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] PHP non detecte dans le PATH.
    echo.
    pause
    exit /b 1
)

if not exist "vendor\bin\phpunit.bat" (
    echo [ERREUR] PHPUnit introuvable : vendor\bin\phpunit.bat
    echo.
    echo Verifie que Composer a bien installe les dependances.
    echo.
    pause
    exit /b 1
)

if not exist "phpunit.xml" (
    echo [ERREUR] phpunit.xml introuvable a la racine du projet.
    echo.
    pause
    exit /b 1
)

if not exist "tests\reports" (
    mkdir "tests\reports"
)

echo Lancement du coverage...
echo.

call vendor\bin\phpunit.bat --testsuite Unit --coverage-html tests/reports/coverage

echo.
echo Coverage genere dans :
echo tests\reports\coverage
echo.

echo Ouvre ce fichier :
echo tests\reports\coverage\index.html
echo.

pause