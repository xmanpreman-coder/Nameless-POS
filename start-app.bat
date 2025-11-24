@echo off
REM Nameless POS - Windows Startup Batch

REM Get the directory where this batch file is located
set APPDIR=%~dp0

REM Change to app directory
cd /d "%APPDIR%"

REM Check if node_modules exists
if not exist "node_modules" (
    echo Installing dependencies...
    call npm install
)

REM Check if electron exists
if not exist "node_modules\electron" (
    echo Installing Electron...
    call npm install electron
)

REM Start the app
echo Starting Nameless POS...
call npx electron .

REM Exit
exit /b 0
