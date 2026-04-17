@echo off

echo.
echo ======================================
echo          LANCEMENT DES TESTS
echo ======================================
echo.

cd /d "%~dp0"

php -v >nul 2>&1

if errorlevel 1 (
    echo ERREUR : PHP n'est pas disponible dans le PATH.
    echo.
    pause
    exit /b 1
)

php "run-tests.php"

echo.
echo ======================================
echo            FIN DES TESTS
echo ======================================
echo.

pause