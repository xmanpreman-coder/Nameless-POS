<#
Download a PHP for Windows (thread-safe CLI zip) and extract to resources\bundle\php
Usage: Run from repository root in PowerShell (run as admin if writing to Program Files)

Note: This helper downloads the official PHP builds from windows.php.net. Adjust the $phpVersion variable to pin a version.
#>

param(
    [string]$phpVersion = '8.1.30',
    [string]$arch = 'x64'
)

$base = "https://windows.php.net/downloads/releases/archives"
$zipName = "php-${phpVersion}-win-${arch}.zip"
$url = "${base}/${zipName}"

Write-Host "Downloading PHP $phpVersion ($arch) from $url"

$target = Join-Path -Path $PSScriptRoot -ChildPath "..\..\resources\bundle\php"
$tmp = Join-Path -Path $env:TEMP -ChildPath $zipName

New-Item -ItemType Directory -Force -Path $target | Out-Null

Invoke-WebRequest -Uri $url -OutFile $tmp -UseBasicParsing

Write-Host "Extracting to $target"
Expand-Archive -Path $tmp -DestinationPath $target -Force

Write-Host "Cleaning temporary file"
Remove-Item $tmp -Force

Write-Host "PHP runtime prepared at: $target"

Write-Host "Remember to copy required extensions (php_sqlite3.dll etc.) and adjust php.ini settings for production." -ForegroundColor Yellow
