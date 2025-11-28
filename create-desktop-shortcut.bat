@echo off
REM Create a shortcut for Nameless POS on Desktop

setlocal

set "SCRIPT_DIR=%~dp0"
set "DESKTOP=%USERPROFILE%\Desktop"
set "SHORTCUT_PATH=%DESKTOP%\Nameless POS.lnk"

REM Create VBScript to make shortcut (simpler than PowerShell)
set "VBS_FILE=%TEMP%\create_shortcut.vbs"

(
echo Set oWS = WScript.CreateObject("WScript.Shell"^)
echo sLinkFile = "%SHORTCUT_PATH%"
echo Set oLink = oWS.CreateShortcut(sLinkFile^)
echo oLink.TargetPath = "%SCRIPT_DIR%run-localhost.bat"
echo oLink.WorkingDirectory = "%SCRIPT_DIR%"
echo oLink.Description = "Nameless POS - Open in Browser"
echo oLink.IconLocation = "%SCRIPT_DIR%icon.ico"
echo oLink.Save
) > "%VBS_FILE%"

cscript.exe "%VBS_FILE%"
del "%VBS_FILE%"

echo.
echo Shortcut created on Desktop: Nameless POS
echo.
pause
