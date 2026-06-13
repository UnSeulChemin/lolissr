@echo off

echo.
echo ==========================
echo      CLEAR CACHE
echo ==========================
echo.

for %%f in (..\storage\cache\*) do (
    if /I not "%%~nxf"==".gitkeep" (
        del /f /q "%%f"
    )
)

echo.
echo Cache vide.
echo.

pause