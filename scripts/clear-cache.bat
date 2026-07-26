@echo off
setlocal EnableExtensions

title LoliSSR - Clear Cache Quest
cls

cd /d "%~dp0.."

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                  Quest : Clear Cache
echo.
echo ============================================================
echo.

echo [SYSTEM]
echo Removing cache files...
echo.

if not exist "storage\cache\" (
    echo.
    echo [FAILED]
    echo.
    echo The cache directory could not be found.
    echo.
    pause
    exit /b 1
)

for %%F in ("storage\cache\*") do (
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
echo            All cache files were removed.
echo.
echo ============================================================
echo.

pause
exit /b 0