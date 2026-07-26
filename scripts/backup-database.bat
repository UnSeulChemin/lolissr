@echo off
setlocal EnableExtensions

title LoliSSR - Database Backup Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                 Quest : Database Backup
echo.
echo ============================================================
echo.

echo [SYSTEM]
echo Locating archives...
echo Connecting to database...
echo Preparing storage vault...
echo Generating SQL artifact...
echo.
echo Quest started.
echo.

php scripts\backup-database.php

if errorlevel 1 (
    echo.
    echo ============================================================
    echo.
    echo                    QUEST FAILED
    echo.
    echo        The database artifact could not be created.
    echo.
    echo        Review the errors displayed above.
    echo.
    echo ============================================================
    echo.

    pause
    exit /b 1
)

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo          The kingdom archives are secured.
echo.
echo      A new database artifact has been created.
echo.
echo ============================================================
echo.

pause
exit /b 0