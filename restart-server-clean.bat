@echo off
echo 🔧 Restarting Laravel Server (Clean)
echo.

echo Step 1: Stopping any existing servers...
taskkill /F /IM php.exe 2>nul
timeout /t 2 /nobreak > nul

echo Step 2: Clearing all Laravel caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo Step 3: Checking Laravel installation...
php artisan --version

echo Step 4: Testing basic route...
curl -s http://127.0.0.1:8000/test 2>nul || echo "Server not running yet"

echo.
echo Step 5: Starting fresh server...
echo 🚀 Server starting at: http://127.0.0.1:8000
echo 📱 Frontend should connect to: http://127.0.0.1:8000/api
echo.
echo ⚠️  Keep this window open while using the app
echo 🛑 Press Ctrl+C to stop the server
echo.

php artisan serve --host=127.0.0.1 --port=8000