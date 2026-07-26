@echo off
setlocal EnableExtensions

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

if errorlevel 1 (
    call :failure "The cache could not be cleared."
    exit /b 1
)

echo.
echo [SYSTEM]
echo Clearing sessions...
call scripts\clear-sessions.bat

if errorlevel 1 (
    call :failure "The sessions could not be cleared."
    exit /b 1
)

echo.
echo [SYSTEM]
echo Clearing logs...
call scripts\clear-logs.bat

if errorlevel 1 (
    call :failure "The logs could not be cleared."
    exit /b 1
)

echo.
echo [SYSTEM]
echo Regenerating Composer autoload...

composer --version >nul 2>&1

if errorlevel 1 (
    call :failure "Composer could not be found."
    exit /b 1
)

composer dump-autoload

if errorlevel 1 (
    call :failure "Composer autoload could not be regenerated."
    exit /b 1
)

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo      Cache cleared.
echo      Sessions cleared.
echo      Logs cleared.
echo      Autoload regenerated.
echo.
echo      The development environment is refreshed.
echo.
echo ============================================================
echo.

pause
exit /b 0


:failure
echo.
echo ============================================================
echo.
echo                    QUEST FAILED
echo.
echo        %~1
echo.
echo        Review the errors displayed above.
echo.
echo ============================================================
echo.

pause
exit /b 1