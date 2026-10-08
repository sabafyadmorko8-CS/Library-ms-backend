@echo off
echo 🔧 Setting up users for Library Management System...
echo.

echo Step 1: Creating demo users...
curl -s "http://127.0.0.1:8000/api/create-demo-users" -H "Accept: application/json"

echo.
echo.
echo Step 2: Creating Sabaf admin user...
curl -s "http://127.0.0.1:8000/api/create-sabaf-admin" -H "Accept: application/json"

echo.
echo.
echo Step 3: Checking all users...
curl -s "http://127.0.0.1:8000/api/debug-users" -H "Accept: application/json"

echo.
echo.
echo ✅ User setup complete!
echo.
echo You can now login with:
echo 📧 sabaf@admin.com / password (Admin)
echo 📧 admin@library.com / password (Admin)
echo 📧 librarian@library.com / password (Librarian)
echo 📧 user@library.com / password (User)
echo.
pause