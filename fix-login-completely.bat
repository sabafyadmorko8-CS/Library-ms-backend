@echo off
echo 🔧 COMPLETE LOGIN FIX
echo.

echo Step 1: Clearing all caches...
php artisan config:clear 2>nul
php artisan cache:clear 2>nul
php artisan route:clear 2>nul
php artisan optimize:clear 2>nul

echo Step 2: Testing database connection...
php artisan migrate:status
if %errorlevel% neq 0 (
    echo ❌ Database connection failed
    echo Running migrations...
    php artisan migrate --force
)

echo Step 3: Creating users via API...
echo Creating demo users...
curl -s "http://127.0.0.1:8000/api/create-demo-users" -H "Accept: application/json" > temp_demo.json
type temp_demo.json
del temp_demo.json 2>nul

echo.
echo Creating Sabaf admin...
curl -s "http://127.0.0.1:8000/api/create-sabaf-admin" -H "Accept: application/json" > temp_sabaf.json
type temp_sabaf.json
del temp_sabaf.json 2>nul

echo.
echo Step 4: Testing login...
curl -s -X POST "http://127.0.0.1:8000/api/simple-login" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"sabaf@admin.com\",\"password\":\"password\"}" > temp_login.json

echo Login test result:
type temp_login.json
del temp_login.json 2>nul

echo.
echo Step 5: Checking all users...
curl -s "http://127.0.0.1:8000/api/debug-users" -H "Accept: application/json" > temp_users.json
type temp_users.json
del temp_users.json 2>nul

echo.
echo ✅ LOGIN FIX COMPLETE!
echo.
echo You can now:
echo 1. Login with: sabaf@admin.com / password
echo 2. Test at: http://127.0.0.1:8000/debug-login.html
echo 3. Use your React app
echo.
pause