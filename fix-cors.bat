@echo off
echo Fixing CORS issue...
echo.

echo Step 1: Installing CORS package...
composer require fruitcake/laravel-cors

echo Step 2: Publishing CORS config...
php artisan vendor:publish --tag="cors"

echo Step 3: Clearing config cache...
php artisan config:clear

echo Step 4: Restarting server...
echo.
echo ✅ CORS should now be fixed!
echo ✅ Frontend should be able to connect to backend
echo.

php artisan serve