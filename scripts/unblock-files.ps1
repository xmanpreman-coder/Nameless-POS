<#
Unblock all executable files that Windows may mark as blocked (downloaded from internet).
Run as Administrator (recommended).
#>
Write-Host "Unblocking common executable files under project..." -ForegroundColor Cyan
$paths = @(
    "./node_modules",
    "./dist",
    "./build",
    "./vendor"
)

foreach ($p in $paths) {
    if (Test-Path $p) {
        Write-Host "Scanning: $p" -ForegroundColor Yellow
        Get-ChildItem -Path $p -Recurse -Force -ErrorAction SilentlyContinue | Where-Object { $_.Extension -ieq '.exe' -or $_.Extension -ieq '.dll' } | ForEach-Object {
            try {
                Unblock-File -Path $_.FullName -ErrorAction Stop
                Write-Host "Unblocked: $($_.FullName)" -ForegroundColor Green
            } catch {
                Write-Host "Failed to unblock: $($_.FullName) - $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    }
}
Write-Host "Unblock operation completed." -ForegroundColor Green
