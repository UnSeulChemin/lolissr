@echo off
title LoliSSR - Development Reset Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                 Quest : Development Reset
echo.
echo ============================================================
echo.

echo [SYSTEM]
echo Clearing cache...
call scripts\clear-cache.bat

echo.
echo [SYSTEM]
echo Clearing sessions...
call scripts\clear-sessions.bat

echo.
echo [SYSTEM]
echo Regenerating Composer autoload...
composer dump-autoload

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo      The development environment is refreshed.
echo.
echo ============================================================
echo.

pause