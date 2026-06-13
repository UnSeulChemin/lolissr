@echo off
title LoliSSR - PHPStan Inspection Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                 Quest : Code Inspection
echo.
echo ============================================================
echo.

echo [SYSTEM]
echo Summoning the Oracle...
echo Scanning controllers...
echo Scanning services...
echo Scanning repositories...
echo Verifying type safety...
echo.
echo Quest started.
echo.

call vendor\bin\phpstan analyse

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo          The codebase has been inspected.
echo.
echo      Review the report for hidden anomalies.
echo.
echo ============================================================
echo.

pause