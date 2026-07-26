@echo off
setlocal EnableExtensions

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

if not exist "vendor\bin\phpstan.bat" (
    echo.
    echo [FAILED]
    echo.
    echo The PHPStan Oracle could not be found.
    echo Run composer install before starting this quest.
    echo.
    pause
    exit /b 1
)

echo [SYSTEM]
echo Summoning the Oracle...
echo Scanning controllers...
echo Scanning services...
echo Scanning repositories...
echo Verifying type safety...
echo.
echo Quest started.
echo.

call vendor\bin\phpstan.bat analyse

if errorlevel 1 (
    echo.
    echo ============================================================
    echo.
    echo                    QUEST FAILED
    echo.
    echo          The Oracle detected code anomalies.
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
echo          The codebase has been inspected.
echo.
echo          No type anomalies were detected.
echo.
echo ============================================================
echo.

pause
exit /b 0