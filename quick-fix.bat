@echo off
echo Quick Fix for Laravel Backend...
echo.

echo Step 1: Installing Sanctum...
composer require laravel/sanctum

echo Step 2: Clearing all caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo Step 3: Running migrations and seeders...
php artisan migrate:fresh --seed

echo Step 4: Starting server...
echo.
echo ✅ Server starting at http://localhost:8000
echo ✅ Test API: http://localhost:8000/api/test
echo ✅ Debug users: http://localhost:8000/api/debug-users
echo ✅ Login test: http://localhost:8000/test-login.html
echo.

php artisan serve