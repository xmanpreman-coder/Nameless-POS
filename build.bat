@echo off
REM Build Nameless POS EXE - Bypass PowerShell Issues
cd /d "d:\project warnet\Nameless"

echo Cleaning dist...
rmdir /s /q dist 2>nul

echo Clearing npm cache...
call npm cache clean --force >nul 2>&1

echo Starting build...
echo This may take 5-10 minutes - DO NOT INTERRUPT
echo.

call npm run dist

echo.
echo Build complete. Checking for EXE...
if exist "dist\*.exe" (
    echo SUCCESS! EXE files created:
    dir dist\*.exe /B
) else (
    echo Build may have failed. Check above for errors.
)

pause
