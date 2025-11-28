@echo off
setlocal enabledelayedexpansion

REM Nameless POS - Launcher Script
REM This script starts Laravel server and opens the app in your default browser

cd /d "%~dp0"

REM Check PHP installation
php -v >nul 2>&1
if errorlevel 1 (
    color 0C
    echo.
    echo ===============================
    echo ERROR: PHP NOT FOUND
    echo ===============================
    echo.
    echo Nameless POS requires PHP 8.0 or higher
    echo.
    echo SOLUTION:
    echo 1. Download PHP from: https://windows.php.net/download/
    echo 2. Extract to a folder (e.g., C:\PHP)
    echo 3. Add to Windows PATH environment variable
    echo 4. Verify: Open CMD and type "php -v"
    echo.
    echo OR install XAMPP/WampServer
    echo https://www.xampp.com
    echo.
    pause
    exit /b 1
)

REM Kill any existing Laravel servers on port 8000
for /f "tokens=5" %%a in ('netstat -ano 2^>nul ^| findstr ":8000"') do (
    taskkill /pid %%a /f >nul 2>&1
)

cls
color 0A

echo.
echo ===============================
echo   Nameless POS - Starting...
echo ===============================
echo.

REM Start Laravel server in background
start /B cmd /c "php artisan serve --host=127.0.0.1 --port=8000 > nul 2>&1"

REM Wait for server to start
echo   Waiting for server to start...
timeout /t 2 /nobreak >nul

REM Open in default browser
echo   Opening browser...
start http://127.0.0.1:8000

echo.
echo ===============================
echo ✓ Nameless POS is running!
echo ===============================
echo.
echo Access the app at:
echo   http://127.0.0.1:8000
echo.
echo Press Ctrl+C in the Laravel server window to stop
echo.
pause
