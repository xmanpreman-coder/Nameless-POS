@echo off
REM ========================================
REM Nameless POS - Offline Mode Launcher
REM NO XAMPP NEEDED!
REM ========================================

setlocal enabledelayedexpansion

echo.
echo ========================================
echo  Nameless POS - Offline Mode Launcher
echo ========================================
echo.
echo Starting backend (no XAMPP needed)...
echo.

REM Get directory
cd /d "%~dp0"

REM Check if PHP exists
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo.
    echo ERROR: PHP not found in PATH!
    echo.
    echo Please ensure PHP is installed and added to system PATH.
    echo You can download PHP from: https://www.php.net/downloads
    echo.
    pause
    exit /b 1
)

REM Check if .env exists, if not create from .env.example
if not exist ".env" (
    echo [INFO] Creating .env file from .env.example...
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo [OK] .env created
    ) else (
        echo [WARNING] .env.example not found, creating default .env...
        (
            echo APP_NAME=Nameless POS
            echo APP_ENV=production
            echo APP_DEBUG=false
            echo APP_URL=http://localhost:8000
            echo DB_CONNECTION=sqlite
            echo DB_DATABASE=./database/app.sqlite
        ) > .env
        echo [OK] Default .env created
    )
)

REM Check if database exists
if not exist "database\app.sqlite" (
    echo [INFO] Creating database...
    php artisan migrate --force >nul 2>nul
    if %errorlevel% equ 0 (
        echo [OK] Database created
    ) else (
        echo [WARNING] Database migration failed
    )
)

REM Kill any existing PHP processes on port 8000
echo [INFO] Checking for existing servers...
netstat -ano | findstr ":8000" >nul 2>nul
if %errorlevel% equ 0 (
    echo [INFO] Found existing server, stopping it...
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":8000"') do (
        taskkill /F /PID %%a >nul 2>nul
    )
    timeout /t 2 /nobreak >nul
)

REM Start Laravel backend in background (hidden window)
echo [INFO] Starting Laravel backend on localhost:8000...
start /B "" php artisan serve --host=localhost --port=8000

REM Wait for backend to be ready
echo [INFO] Waiting for backend to start (up to 10 seconds)...
set count=0
:waitloop
timeout /t 1 /nobreak >nul
set /a count+=1
if %count% gtr 10 (
    echo [WARNING] Timeout waiting for backend
    goto chrome_launch
)

REM Test if backend is responding
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost:8000' -TimeoutSec 2 -ErrorAction SilentlyContinue; if ($response.StatusCode -eq 200) { exit 0 } } catch { exit 1 }"
if %errorlevel% equ 0 (
    echo [OK] Backend is ready!
    goto chrome_launch
)
goto waitloop

:chrome_launch
echo.

REM Try to find Chrome installation
set CHROME_PATH=
if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    set CHROME_PATH=C:\Program Files\Google\Chrome\Application\chrome.exe
) else if exist "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" (
    set CHROME_PATH=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe
) else if exist "%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe" (
    set CHROME_PATH=%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe
)

if "!CHROME_PATH!"=="" (
    echo.
    echo [ERROR] Google Chrome not found!
    echo.
    echo Please install Google Chrome from:
    echo https://www.google.com/chrome/
    echo.
    echo Alternative: Open browser manually and go to:
    echo http://localhost:8000
    echo.
    pause
    exit /b 1
)

REM Launch Chrome as PWA app
echo [INFO] Opening Nameless POS in Chrome...
echo [INFO] Chrome is opening in app mode (fullscreen)...
start "" "!CHROME_PATH!" --app=http://localhost:8000 --disable-sync --disable-plugins

REM Show info
echo.
echo ========================================
echo   Nameless POS is now running!
echo ========================================
echo.
echo Backend:       http://localhost:8000
echo Mode:          Offline-First PWA
echo Offline:       YES - Works without internet
echo XAMPP:         NOT NEEDED
echo.
echo Features enabled:
echo  - Offline mode (works without internet)
echo  - Auto-sync when online
echo  - Request queuing
echo  - Service Worker caching
echo.
echo To stop: Close this window OR the Chrome window
echo.
pause
