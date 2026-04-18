@echo off
cd /d %~dp0\..

echo.
echo ================================
echo   LoliSSR - Tests UNITAIRES
echo ================================
echo.

REM Lancer PHPUnit
vendor\bin\phpunit

echo.
echo ================================
echo   Fin des tests unitaires
echo ================================
echo.

pause