@echo off
setlocal EnableExtensions

title LoliSSR - Build Release Quest
cls

cd /d "%~dp0.."

set "RELEASES_DIR=releases"
set "TEMP_ROOT=%RELEASES_DIR%\.build-temp"

echo.
echo ============================================================
echo.
echo               ^>^> LOLISSR ADVENTURER GUILD ^<^<
echo.
echo                   Quest : Build Release
echo.
echo ============================================================
echo.

echo [SYSTEM]
echo Reading application configuration...
echo.

if not exist ".env" (
    echo [ERROR] Missing configuration file: .env
    goto :error
)

set "PROJECT_NAME="
set "VERSION="

for /f "usebackq tokens=1,* delims==" %%A in (".env") do (
    if /i "%%A"=="APP_NAME" set "PROJECT_NAME=%%B"
    if /i "%%A"=="APP_VERSION" set "VERSION=%%B"
)

if not defined PROJECT_NAME (
    echo [ERROR] APP_NAME was not found in .env
    goto :error
)

if not defined VERSION (
    echo [ERROR] APP_VERSION was not found in .env
    goto :error
)

set "RELEASE_NAME=%PROJECT_NAME%_v%VERSION%"
set "BUILD_DIR=%TEMP_ROOT%\%RELEASE_NAME%"
set "ZIP_PATH=%RELEASES_DIR%\%RELEASE_NAME%.zip"

echo Application : %PROJECT_NAME%
echo Version     : %VERSION%
echo Archive     : %ZIP_PATH%

echo.
echo [SYSTEM]
echo Preparing release vault...

if not exist "%RELEASES_DIR%" (
    mkdir "%RELEASES_DIR%"

    if errorlevel 1 (
        echo [ERROR] Unable to create directory: %RELEASES_DIR%
        goto :error
    )
)

if exist "%TEMP_ROOT%" (
    echo Removing previous temporary build...
    rmdir /s /q "%TEMP_ROOT%"

    if exist "%TEMP_ROOT%" (
        echo [ERROR] Unable to remove previous temporary build.
        goto :error
    )
)

if exist "%ZIP_PATH%" (
    echo Removing previous release archive...
    del /f /q "%ZIP_PATH%"

    if exist "%ZIP_PATH%" (
        echo [ERROR] Unable to remove previous release archive.
        goto :error
    )
)

mkdir "%BUILD_DIR%"

if errorlevel 1 (
    echo [ERROR] Unable to create temporary build directory.
    goto :error
)

echo.
echo [SYSTEM]
echo Copying application directories...

call :copyDirectory "App"
if errorlevel 1 goto :error

call :copyDirectory "Config"
if errorlevel 1 goto :error

call :copyDirectory "Framework"
if errorlevel 1 goto :error

call :copyPublic
if errorlevel 1 goto :error

call :copyDirectory "scripts"
if errorlevel 1 goto :error

call :copyDirectory "vendor"
if errorlevel 1 goto :error

echo.
echo [SYSTEM]
echo Copying root files...

call :copyFile "composer.json"
if errorlevel 1 goto :error

call :copyFile "composer.lock"
if errorlevel 1 goto :error

call :copyFile ".env.example"
if errorlevel 1 goto :error

if exist ".htaccess" (
    call :copyFile ".htaccess"
    if errorlevel 1 goto :error
)

if exist "README.md" (
    call :copyFile "README.md"
    if errorlevel 1 goto :error
)

echo.
echo [SYSTEM]
echo Creating clean runtime directories...

call :createDirectory "storage"
if errorlevel 1 goto :error

call :createDirectory "storage\cache"
if errorlevel 1 goto :error

call :createDirectory "storage\logs"
if errorlevel 1 goto :error

call :createDirectory "storage\sessions"
if errorlevel 1 goto :error

call :createDirectory "storage\backups"
if errorlevel 1 goto :error

call :createDirectory "storage\backups\database"
if errorlevel 1 goto :error

call :createDirectory "public\images"
if errorlevel 1 goto :error

call :copyGitkeep "storage\cache"
if errorlevel 1 goto :error

call :copyGitkeep "storage\logs"
if errorlevel 1 goto :error

call :copyGitkeep "storage\sessions"
if errorlevel 1 goto :error

call :copyGitkeep "storage\backups"
if errorlevel 1 goto :error

call :copyGitkeep "storage\backups\database"
if errorlevel 1 goto :error

call :copyGitkeep "public\images"
if errorlevel 1 goto :error

echo.
echo [SYSTEM]
echo Verifying forbidden artifacts...

if exist "%BUILD_DIR%\.env" (
    echo [ERROR] Forbidden file detected: .env
    goto :error
)

if exist "%BUILD_DIR%\.git" (
    echo [ERROR] Forbidden directory detected: .git
    goto :error
)

if exist "%BUILD_DIR%\tests" (
    echo [ERROR] Forbidden directory detected: tests
    goto :error
)

if exist "%BUILD_DIR%\releases" (
    echo [ERROR] Forbidden directory detected: releases
    goto :error
)

dir /s /b "%BUILD_DIR%\*.log" >nul 2>&1

if not errorlevel 1 (
    echo [ERROR] A log file was detected in the release.
    goto :error
)

dir /s /b "%BUILD_DIR%\*.sql" >nul 2>&1

if not errorlevel 1 (
    echo [ERROR] A database backup was detected in the release.
    goto :error
)

dir /s /b "%BUILD_DIR%\*.zip" "%BUILD_DIR%\*.rar" "%BUILD_DIR%\*.7z" >nul 2>&1

if not errorlevel 1 (
    echo [ERROR] An archive was detected inside the release.
    goto :error
)

echo Verification completed.

echo.
echo [SYSTEM]
echo Compressing release artifact...

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
    "$items = Get-ChildItem -LiteralPath $env:BUILD_DIR -Force; if (-not $items) { exit 1 }; Compress-Archive -Path $items.FullName -DestinationPath $env:ZIP_PATH -CompressionLevel Optimal -Force"

if errorlevel 1 (
    echo [ERROR] PowerShell failed to create the release archive.
    goto :error
)

if not exist "%ZIP_PATH%" (
    echo [ERROR] The release archive was not created.
    goto :error
)

echo.
echo [SYSTEM]
echo Removing temporary build directory...

rmdir /s /q "%TEMP_ROOT%"

if exist "%TEMP_ROOT%" (
    echo [ERROR] Unable to remove the temporary build directory.
    goto :error
)

echo.
echo ============================================================
echo.
echo                  QUEST COMPLETED
echo.
echo        The release artifact has been forged.
echo.
echo        Sensitive files have been excluded.
echo.
echo        Runtime directories have been cleaned.
echo.
echo        Temporary files have been removed.
echo.
echo        Archive:
echo        %ZIP_PATH%
echo.
echo ============================================================
echo.

pause
exit /b 0


:copyDirectory
set "SOURCE_DIRECTORY=%~1"

if not exist "%SOURCE_DIRECTORY%\" (
    echo [ERROR] Missing directory: %SOURCE_DIRECTORY%
    exit /b 1
)

robocopy "%SOURCE_DIRECTORY%" "%BUILD_DIR%\%SOURCE_DIRECTORY%" /E /R:2 /W:1 /NFL /NDL /NJH /NJS /NP >nul

if errorlevel 8 (
    echo [ERROR] Failed to copy directory: %SOURCE_DIRECTORY%
    exit /b 1
)

exit /b 0


:copyPublic
if not exist "public\" (
    echo [ERROR] Missing directory: public
    exit /b 1
)

robocopy "public" "%BUILD_DIR%\public" /E /R:2 /W:1 /NFL /NDL /NJH /NJS /NP /XD "%CD%\public\images" >nul

if errorlevel 8 (
    echo [ERROR] Failed to copy directory: public
    exit /b 1
)

exit /b 0


:copyFile
set "SOURCE_FILE=%~1"

if not exist "%SOURCE_FILE%" (
    echo [ERROR] Missing file: %SOURCE_FILE%
    exit /b 1
)

copy /y "%SOURCE_FILE%" "%BUILD_DIR%\" >nul

if errorlevel 1 (
    echo [ERROR] Failed to copy file: %SOURCE_FILE%
    exit /b 1
)

exit /b 0


:createDirectory
set "DIRECTORY_PATH=%~1"

if not exist "%BUILD_DIR%\%DIRECTORY_PATH%\" (
    mkdir "%BUILD_DIR%\%DIRECTORY_PATH%"
)

if not exist "%BUILD_DIR%\%DIRECTORY_PATH%\" (
    echo [ERROR] Failed to create directory: %DIRECTORY_PATH%
    exit /b 1
)

exit /b 0


:copyGitkeep
set "GITKEEP_DIRECTORY=%~1"

if not exist "%GITKEEP_DIRECTORY%\.gitkeep" (
    exit /b 0
)

copy /y "%GITKEEP_DIRECTORY%\.gitkeep" "%BUILD_DIR%\%GITKEEP_DIRECTORY%\" >nul

if errorlevel 1 (
    echo [ERROR] Failed to copy .gitkeep from: %GITKEEP_DIRECTORY%
    exit /b 1
)

exit /b 0


:error
echo.
echo ============================================================
echo.
echo                    QUEST FAILED
echo.
echo       The release artifact could not be created.
echo.
echo       Review the errors displayed above.
echo.
echo ============================================================
echo.

if defined TEMP_ROOT (
    if exist "%TEMP_ROOT%" rmdir /s /q "%TEMP_ROOT%"
)

pause
exit /b 1