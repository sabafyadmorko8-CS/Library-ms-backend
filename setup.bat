@echo off
echo === Laravel Backend Setup ===

echo Step 1: Installing Composer dependencies...
composer install

echo Step 2: Generating application key...
php artisan key:generate

echo Step 3: Running migrations and seeders...
php artisan migrate:fresh --seed

echo Step 4: Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo === Setup Complete! ===
echo.
echo To start the server, run:
echo php artisan serve
echo.
echo Then visit: http://localhost:8000/test
pause