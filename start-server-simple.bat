@echo off
echo 🚀 Starting Laravel Server...
echo.

echo Clearing caches...
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan cache:clear >nul 2>&1

echo.
echo Starting server at: http://127.0.0.1:8000
echo.
echo ⚠️  IMPORTANT: Keep this window open!
echo 🛑 Press Ctrl+C to stop the server
echo.
echo 📱 Your frontend should connect to: http://127.0.0.1:8000/api
echo 🌐 Test in browser: http://127.0.0.1:8000/test
echo.

php artisan serve --host=127.0.0.1 --port=8000