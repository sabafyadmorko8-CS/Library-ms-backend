@echo off
echo 🔍 Diagnosing 404 Error...
echo.

echo Step 1: Checking if PHP is available...
php --version
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed or not in PATH
    pause
    exit /b 1
)

echo.
echo Step 2: Checking Laravel installation...
php artisan --version
if %errorlevel% neq 0 (
    echo ❌ Laravel is not properly installed
    pause
    exit /b 1
)

echo.
echo Step 3: Checking if port 8000 is free...
netstat -an | find "8000" | find "LISTENING"
if %errorlevel% equ 0 (
    echo ⚠️  Port 8000 is already in use
    echo Trying to kill existing processes...
    taskkill /F /IM php.exe 2>nul
    timeout /t 2 /nobreak > nul
)

echo.
echo Step 4: Clearing Laravel caches...
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo.
echo Step 5: Testing basic Laravel functionality...
php artisan route:list | find "api/test"
if %errorlevel% neq 0 (
    echo ❌ API routes not found
) else (
    echo ✅ API routes are registered
)

echo.
echo Step 6: Starting server...
echo 🚀 Server will start at: http://127.0.0.1:8000
echo 🧪 Test URL: http://127.0.0.1:8000/test-server.html
echo.
echo ⚠️  Keep this window open!
echo.

php artisan serve --host=127.0.0.1 --port=8000