@echo off
echo Restarting Laravel Server...
echo.

echo Step 1: Clearing all caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo Step 2: Checking database...
if not exist "database\database.sqlite" (
    echo Creating database...
    type nul > database\database.sqlite
    php artisan migrate:fresh --seed
) else (
    echo Database exists, running seeders...
    php artisan db:seed --force
)

echo Step 3: Starting server...
echo.
echo ✅ Server starting at http://localhost:8000
echo ✅ Test: http://localhost:8000/api/test
echo ✅ Debug: http://localhost:8000/api/debug-users
echo ✅ Login: http://localhost:8000/test-login.html
echo.

php artisan serve