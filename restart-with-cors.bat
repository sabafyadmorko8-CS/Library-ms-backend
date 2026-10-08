@echo off
echo Restarting Laravel with CORS support...
echo.

echo Step 1: Clearing config cache...
php artisan config:clear

echo Step 2: Clearing route cache...
php artisan route:clear

echo Step 3: Starting server with CORS enabled...
echo.
echo ✅ CORS middleware added
echo ✅ Server starting at http://127.0.0.1:8000
echo ✅ Frontend at http://localhost:3000 should now connect
echo.
echo Test CORS: http://localhost:8000/test-cors.html
echo.

php artisan serve