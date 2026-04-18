@echo off
cd /d %~dp0\..

echo.
echo ================================
echo   LoliSSR - Coverage PHPUnit
echo ================================
echo.

php -d zend_extension="C:\wamp64\bin\php\php8.3.0\ext\php_xdebug.dll" ^
vendor\bin\phpunit ^
--coverage-html tests\reports\coverage

echo.
echo Coverage généré dans :
echo tests\reports\coverage
echo.

pause