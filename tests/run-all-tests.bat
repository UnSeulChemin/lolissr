@echo off
cd /d %~dp0\..

echo.
echo ========================================
echo        LoliSSR - TESTS COMPLETS
echo ========================================
echo.

echo [1/3] Tests unitaires PHPUnit...
call tests\run-unit-tests.bat

echo.
echo [2/3] Coverage PHPUnit...
call tests\run-unit-coverage.bat

echo.
echo [3/3] Tests HTTP...
call tests\run-tests.bat

echo.
echo ========================================
echo        TOUS LES TESTS SONT FINIS
echo ========================================
echo.

pause