@echo off
echo 🚨 EMERGENCY FIX - Laravel Server Error
echo.

echo Step 1: Killing any existing PHP processes...
taskkill /F /IM php.exe 2>nul
timeout /t 2 /nobreak > nul

echo Step 2: Clearing ALL Laravel caches...
php artisan config:clear 2>nul
php artisan cache:clear 2>nul
php artisan route:clear 2>nul
php artisan view:clear 2>nul
php artisan optimize:clear 2>nul

echo Step 3: Removing cached files manually...
if exist bootstrap\cache\config.php del bootstrap\cache\config.php 2>nul
if exist bootstrap\cache\routes-v7.php del bootstrap\cache\routes-v7.php 2>nul
if exist bootstrap\cache\services.php del bootstrap\cache\services.php 2>nul

echo Step 4: Testing Laravel installation...
php artisan --version
if %errorlevel% neq 0 (
    echo ❌ Laravel installation issue detected
    pause
    exit /b 1
)

echo Step 5: Testing basic PHP functionality...
php -r "echo 'PHP is working: ' . PHP_VERSION . PHP_EOL;"

echo Step 6: Starting server with verbose output...
echo.
echo 🚀 Starting Laravel server...
echo 📍 URL: http://127.0.0.1:8000
echo 🧪 Test: http://127.0.0.1:8000/api/test
echo.
echo ⚠️  If you see errors, press Ctrl+C and run this script again
echo.

php artisan serve --host=127.0.0.1 --port=8000 --verbose