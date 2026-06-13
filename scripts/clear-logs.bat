@echo off

echo.
echo ==========================
echo        CLEAR LOGS
echo ==========================
echo.

for %%f in (storage\logs\*) do (
    if /I not "%%~nxf"==".gitkeep" (
        del /f /q "%%f"
    )
)

echo.
echo Logs vides.
echo.

pause