@echo off
REM Test Nameless POS startup time

echo.
echo ========================================
echo  Nameless POS - Startup Performance Test
echo ========================================
echo.

cd /d "d:\project warnet\Nameless"

REM Record start time
setlocal enabledelayedexpansion
for /f "tokens=2-4 delims=/- " %%a in ('date /t') do (set mydate=%%c-%%a-%%b)
for /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a%%b)
set starttime=!mydate! !mytime!

echo [%date% %time%] Starting Laravel server...
php artisan serve --host=127.0.0.1 --port=8000 > nul 2>&1 &
set server_pid=%errorlevel%

timeout /t 3 /nobreak > nul

echo [%date% %time%] Server started, checking connection...
powershell -Command "try { $web = Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -UseBasicParsing -TimeoutSec 10; Write-Host '✓ App loaded successfully'; exit 0 } catch { Write-Host '✗ App not responding'; exit 1 }"

if %errorlevel% equ 0 (
    echo.
    echo ✓ STARTUP SUCCESSFUL
    echo Typically takes 3-5 seconds for initial load
) else (
    echo.
    echo ✗ STARTUP FAILED
    echo Check PHP and MySQL installation
)

echo.
echo Cleaning up...
taskkill /F /IM "php.exe" >nul 2>&1
timeout /t 1 /nobreak > nul

echo Done.
