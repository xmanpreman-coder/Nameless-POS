@echo off
title Stop Laravel Server

echo Stopping Laravel Server...

REM Kill proses PHP artisan serve
taskkill /F /FI "WINDOWTITLE eq Laravel Server - Nameless Running*" >nul 2>&1

REM Kill semua proses PHP yang jalan di port 8000
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8000 ^| findstr LISTENING') do (
    taskkill /F /PID %%a >nul 2>&1
)

echo.
echo ========================================
echo   Server berhasil dimatikan!
echo ========================================
echo.
timeout /t 2 >nul
exit
