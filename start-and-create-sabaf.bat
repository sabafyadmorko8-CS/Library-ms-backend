@echo off
echo Starting Laravel server and creating Sabaf user...
echo.

echo Step 1: Starting Laravel server...
start "Laravel Server" cmd /k "php artisan serve"

echo Step 2: Waiting for server to start...
timeout /t 3 /nobreak > nul

echo Step 3: Creating Sabaf admin user...
curl -X GET "http://127.0.0.1:8000/api/create-sabaf-admin" -H "Accept: application/json"

echo.
echo.
echo Done! You can now:
echo 1. Login with: sabaf@admin.com / password
echo 2. Or test at: http://127.0.0.1:8000/quick-test.html
echo.
pause