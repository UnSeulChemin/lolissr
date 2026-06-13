@echo off
title LoliSSR - Publish Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                 Quest : Publish Changes
echo.
echo ============================================================
echo.

echo [SYSTEM]
echo Staging files...
echo Preparing commit...
echo Preparing remote portal...
echo.

set /p MESSAGE=Commit message: 

if "%MESSAGE%"=="" (
    echo.
    echo [FAILED]
    echo Commit message is required.
    echo.
    pause
    exit /b 1
)

git add .

git commit -m "%MESSAGE%"

git push origin master

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo         The changes have reached the kingdom.
echo.
echo ============================================================
echo.

pause