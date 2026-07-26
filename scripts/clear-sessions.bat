@echo off
setlocal EnableExtensions

title LoliSSR - Clear Sessions Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                 Quest : Clear Sessions
echo.
echo ============================================================
echo.

echo [SYSTEM]
echo Removing session files...
echo.

if not exist "storage\sessions\" (
    echo.
    echo [FAILED]
    echo.
    echo The sessions directory could not be found.
    echo.
    pause
    exit /b 1
)

for %%F in ("storage\sessions\*") do (
    if /i not "%%~nxF"==".gitkeep" (
        del /f /q "%%F" >nul 2>&1

        if exist "%%F" (
            echo.
            echo [FAILED]
            echo.
            echo Unable to remove: %%~nxF
            echo.
            pause
            exit /b 1
        )
    )
)

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo          All session files were removed.
echo.
echo ============================================================
echo.

pause
exit /b 0