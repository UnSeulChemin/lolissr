@echo off

echo.
echo ==========================
echo     CLEAR SESSIONS
echo ==========================
echo.

for %%f in (..\storage\sessions\*) do (
    if /I not "%%~nxf"==".gitkeep" (
        del /f /q "%%f"
    )
)

echo.
echo Sessions videes.
echo.

pause