@echo off
echo 🔍 Checking Laravel Server Status...
echo.

echo Testing server connection...
curl -s http://127.0.0.1:8000/test 2>nul

if %errorlevel% equ 0 (
    echo ✅ Server is running!
) else (
    echo ❌ Server is NOT running!
    echo.
    echo 🚀 Starting server now...
    echo.
    php artisan serve --host=127.0.0.1 --port=8000
)

pause