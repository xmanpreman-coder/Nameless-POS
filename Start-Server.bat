@echo off
title Laravel Server - Nameless

REM Simpan PID server ke file
cd /d "D:\project warnet\Nameless"

REM Cek apakah server sudah jalan
tasklist /FI "WINDOWTITLE eq Laravel Server - Nameless*" 2>NUL | find /I /N "cmd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo Server sudah berjalan!
    start http://localhost:8000
    timeout /t 2 >nul
    exit
)

REM Start server tanpa tampilkan console
echo Starting server... Please wait...
start /min "" cmd /c "title Laravel Server - Nameless Running && cd /d D:\project warnet\Nameless && php artisan serve --host=127.0.0.1 --port=8000"

REM Tunggu server siap (5 detik)
timeout /t 5 /nobreak >nul

REM Buka browser
start http://localhost:8000

REM Tutup window ini
exit
