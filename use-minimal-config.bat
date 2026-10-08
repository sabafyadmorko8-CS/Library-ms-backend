@echo off
echo 🔧 Switching to minimal Laravel configuration...
echo.

echo Backing up current app.php...
copy bootstrap\app.php bootstrap\app-backup.php 2>nul

echo Switching to minimal configuration...
copy bootstrap\app-minimal.php bootstrap\app.php

echo Clearing caches...
php artisan config:clear 2>nul
php artisan cache:clear 2>nul
php artisan route:clear 2>nul

echo Testing minimal configuration...
php artisan --version

echo.
echo ✅ Minimal configuration activated!
echo 🚀 Starting server...
echo.

php artisan serve --host=127.0.0.1 --port=8000