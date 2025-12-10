@echo off
REM Laravel Optimization Script for Production
REM Run this before building Electron/Tauri exe

echo ========================================
echo Laravel Performance Optimization
echo ========================================

REM Clear all caches first
echo [1/7] Clearing old caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

REM Rebuild optimized caches
echo [2/7] Caching config...
php artisan config:cache

echo [3/7] Caching routes...
php artisan route:cache

echo [4/7] Caching views...
php artisan view:cache

echo [5/7] Caching events...
php artisan event:cache

REM Optimize autoloader
echo [6/7] Optimizing composer autoloader...
composer install --optimize-autoloader --no-dev

REM Optimize icons (if using coreui)
echo [7/7] Caching icons...
php artisan icons:cache 2>nul

echo.
echo ========================================
echo Optimization Complete!
echo ========================================
echo.
echo NOTE: Debugbar has been removed (--no-dev)
echo To add it back for development, run:
echo   composer install
echo.
pause
