@echo off
echo Installing Laravel Sanctum...
echo.

echo Step 1: Installing Sanctum package...
composer require laravel/sanctum

echo Step 2: Publishing Sanctum configuration...
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

echo Step 3: Running migrations...
php artisan migrate

echo Step 4: Clearing config cache...
php artisan config:clear

echo.
echo ✅ Sanctum installation complete!
echo.
pause