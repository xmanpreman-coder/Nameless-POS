# Create Desktop Shortcut for Nameless POS
param(
    [string]$ShortcutName = "Nameless POS"
)

Write-Host "🔗 Creating desktop shortcut..." -ForegroundColor Green

# Get current directory
$currentDir = Get-Location

# Create shortcut on desktop
$desktopPath = [System.Environment]::GetFolderPath('Desktop')
$shortcutPath = Join-Path $desktopPath "$ShortcutName.lnk"

# Create WScript Shell object
$WshShell = New-Object -comObject WScript.Shell

# Create shortcut
$Shortcut = $WshShell.CreateShortcut($shortcutPath)
$Shortcut.TargetPath = "powershell.exe"
$Shortcut.Arguments = "-ExecutionPolicy Bypass -File `"$currentDir\start-pos-auto.ps1`""
$Shortcut.WorkingDirectory = $currentDir
$Shortcut.IconLocation = "$currentDir\public\images\favicon.png"
$Shortcut.Description = "Nameless POS - Point of Sale System"
$Shortcut.WindowStyle = 7  # Minimized
$Shortcut.Save()

Write-Host "Desktop shortcut created: $shortcutPath" -ForegroundColor Green
Write-Host "You can now double-click the shortcut to start POS!" -ForegroundColor Cyan

# Also create Start Menu shortcut
$startMenuPath = "$env:APPDATA\Microsoft\Windows\Start Menu\Programs"
$startMenuShortcut = Join-Path $startMenuPath "$ShortcutName.lnk"

$StartShortcut = $WshShell.CreateShortcut($startMenuShortcut)
$StartShortcut.TargetPath = "powershell.exe"
$StartShortcut.Arguments = "-ExecutionPolicy Bypass -File `"$currentDir\start-pos-auto.ps1`""
$StartShortcut.WorkingDirectory = $currentDir
$StartShortcut.IconLocation = "$currentDir\public\images\favicon.png"
$StartShortcut.Description = "Nameless POS - Point of Sale System"
$StartShortcut.WindowStyle = 7
$StartShortcut.Save()

Write-Host "Start Menu shortcut created" -ForegroundColor Green