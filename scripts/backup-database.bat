@echo off
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

php scripts/backup-database.php

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo ============================================================
echo.

pause