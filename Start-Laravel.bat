@echo off
title Laravel Server - Nameless
color 0A

echo ========================================
echo    Starting Laravel Application
echo    Project: Nameless
echo ========================================
echo.

cd /d "D:\project warnet\Nameless"

echo [1/3] Checking PHP...
php -v >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP not found! Please install PHP first.
    pause
    exit
)
echo PHP OK!
echo.

echo [2/3] Starting Laravel server...
echo Server will run at: http://localhost:8000
echo.

REM Buka browser setelah 3 detik
start "" cmd /c "timeout /t 3 /nobreak >nul && start http://localhost:8000"

echo [3/3] Laravel is running...
echo.
echo ========================================
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php artisan serve --host=127.0.0.1 --port=8000
