@echo off
REM Nameless POS - Browser Launcher
REM Double-click this file to run Nameless POS in your browser

setlocal enabledelayedexpansion

REM Get the directory where this script is located
set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"

REM Check if PHP exists
php -v >nul 2>&1
if errorlevel 1 (
    echo.
    echo ========================================
    echo  ERROR: PHP not found!
    echo ========================================
    echo.
    echo Nameless POS requires PHP to be installed.
    echo Please install PHP and add it to your PATH.
    echo.
    echo Download: https://www.php.net/downloads.php
    echo Or install XAMPP/WampServer
    echo.
    pause
    exit /b 1
)

REM Kill any existing Laravel servers on port 8000
for /f "tokens=5" %%a in ('netstat -ano ^| find ":8000"') do taskkill /pid %%a /f >nul 2>&1

REM Start Laravel server
echo.
echo Starting Nameless POS...
echo.
start /B php artisan serve --host=127.0.0.1 --port=8000

REM Wait for server to start
timeout /t 2 /nobreak >nul

REM Open in browser
start http://127.0.0.1:8000

REM Keep window open
echo.
echo Server started! Browser window opening...
echo.
echo Press Ctrl+C in this window to stop the server
echo.

REM Keep the artisan process running
:wait
timeout /t 1 >nul
goto wait
