@echo off
setlocal EnableExtensions

title LoliSSR - Publish Git Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                   Quest : Publish Git
echo.
echo ============================================================
echo.

git --version >nul 2>&1

if errorlevel 1 (
    echo.
    echo [FAILED]
    echo.
    echo The Git portal could not be found.
    echo Add Git to your PATH before starting this quest.
    echo.
    pause
    exit /b 1
)

git rev-parse --is-inside-work-tree >nul 2>&1

if errorlevel 1 (
    echo.
    echo [FAILED]
    echo.
    echo The current directory is not a Git repository.
    echo.
    pause
    exit /b 1
)

echo [SYSTEM]
echo Staging files...
echo Preparing commit...
echo Preparing remote portal...
echo.

set "MESSAGE="
set /p "MESSAGE=Commit message: "

if not defined MESSAGE (
    echo.
    echo [FAILED]
    echo.
    echo Commit message is required.
    echo.
    pause
    exit /b 1
)

git add .

if errorlevel 1 (
    echo.
    echo ============================================================
    echo.
    echo                    QUEST FAILED
    echo.
    echo             Git could not stage the files.
    echo.
    echo ============================================================
    echo.

    pause
    exit /b 1
)

git diff --cached --quiet

if not errorlevel 1 (
    echo.
    echo [FAILED]
    echo.
    echo No staged changes were found.
    echo Nothing needs to be published.
    echo.
    pause
    exit /b 1
)

git commit -m "%MESSAGE%"

if errorlevel 1 (
    echo.
    echo ============================================================
    echo.
    echo                    QUEST FAILED
    echo.
    echo             Git could not create the commit.
    echo.
    echo ============================================================
    echo.

    pause
    exit /b 1
)

git push origin master

if errorlevel 1 (
    echo.
    echo ============================================================
    echo.
    echo                    QUEST FAILED
    echo.
    echo        The commit could not reach the remote kingdom.
    echo.
    echo        The local commit still exists on this machine.
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
echo         The changes have reached the Git kingdom.
echo.
echo ============================================================
echo.

pause
exit /b 0