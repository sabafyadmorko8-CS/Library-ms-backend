@echo off
echo Starting Laravel Server...
echo.

echo Checking if composer dependencies are installed...
if not exist "vendor" (
    echo Installing composer dependencies...
    composer install
)

echo Checking if .env file exists...
if not exist ".env" (
    echo Copying .env.example to .env...
    copy .env.example .env
)

echo Generating application key...
php artisan key:generate

echo Checking database...
if not exist "database\database.sqlite" (
    echo Creating SQLite database...
    type nul > database\database.sqlite
)

echo Running migrations and seeders...
php artisan migrate:fresh --seed

echo Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo.
echo ===================================
echo Laravel Server Starting...
echo ===================================
echo.
echo Visit: http://localhost:8000
echo API Test: http://localhost:8000/api/test
echo Login Test: http://localhost:8000/test-login.html
echo.

php artisan serve