@echo off
echo Fixing Laravel server error...
echo.

echo Step 1: Clearing all caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo.
echo Step 2: Testing basic functionality...
php artisan --version

echo.
echo Step 3: Starting server...
echo Server will start at: http://127.0.0.1:8000
echo Press Ctrl+C to stop the server
echo.

php artisan serve