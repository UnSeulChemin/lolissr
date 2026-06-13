@echo off
title LoliSSR - Guild Verification Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                 Quest : World Integrity Check
echo.
echo ============================================================
echo.

php -v >nul 2>&1

if errorlevel 1 (
    echo.
    echo [FAILED]
    echo.
    echo The Archmage PHP could not be summoned.
    echo Add PHP to your PATH before starting this quest.
    echo.
    pause
    exit /b 1
)

if not exist "tests\run-tests.php" (
    echo.
    echo [FAILED]
    echo.
    echo The sacred script tests\run-tests.php was not found.
    echo The quest cannot begin.
    echo.
    pause
    exit /b 1
)

echo [SYSTEM]
echo Preparing party...
echo Checking routes...
echo Scanning dungeons...
echo Verifying portals...
echo.
echo Quest started.
echo.

php tests\run-tests.php

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo        The kingdom has been inspected successfully.
echo.
echo      Review the report for corrupted locations.
echo.
echo ============================================================
echo.

pause