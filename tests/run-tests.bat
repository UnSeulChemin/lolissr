@echo off

echo.
echo ======================================
echo          LANCEMENT DES TESTS
echo ======================================
echo.

cd /d %~dp0

php -v >nul 2>&1

if %errorlevel% neq 0 (
    echo ERREUR : PHP n'est pas disponible dans le PATH.
    echo.
    pause
    exit /b
)

php run-tests.php

echo.
echo ======================================
echo            FIN DES TESTS
echo ======================================
echo.

pause