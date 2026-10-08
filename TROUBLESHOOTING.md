# Laravel Backend Troubleshooting Guide

## Quick Fix Steps

### 1. Run the diagnostic script
```bash
php check-backend.php
```

### 2. Run the setup script (Windows)
```bash
setup.bat
```

### 3. Manual setup (if script fails)
```bash
# Install dependencies
composer install

# Generate app key
php artisan key:generate

# Run migrations and seed data
php artisan migrate:fresh --seed

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Start the server
php artisan serve
```

## Common Issues & Solutions

### Issue 1: "Class not found" errors
**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

### Issue 2: Database errors
**Solution:**
```bash
# Check if database file exists
ls -la database/database.sqlite

# If not, create it
touch database/database.sqlite

# Run migrations
php artisan migrate:fresh --seed
```

### Issue 3: "APP_KEY not set" error
**Solution:**
```bash
php artisan key:generate
```

### Issue 4: Permission errors (Linux/Mac)
**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
```

### Issue 5: Sanctum authentication issues
**Solution:**
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Test Endpoints

After starting the server (`php artisan serve`), test these URLs:

1. **Basic Laravel test:**
   - http://localhost:8000/test

2. **API test:**
   - http://localhost:8000/api/test

3. **Register a user:**
   ```bash
   curl -X POST http://localhost:8000/api/register \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test User",
       "email": "test@test.com",
       "password": "password",
       "password_confirmation": "password"
     }'
   ```

4. **Login:**
   ```bash
   curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{
       "email": "admin@library.com",
       "password": "password"
     }'
   ```

## User Authentication

- Users can register and login through the React frontend
- Different user roles have different permissions:
  - **Admin**: Full access to all features
  - **Librarian**: Can manage books and borrows
  - **User**: Can borrow and return books

## File Structure Check

Ensure these files exist:
- ✅ `.env` file with APP_KEY set
- ✅ `database/database.sqlite` file
- ✅ `vendor/` directory (run `composer install`)
- ✅ `bootstrap/cache/` directory with write permissions

## Laravel Logs

Check for errors in:
- `storage/logs/laravel.log`

## Still Having Issues?

1. Check PHP version: `php --version` (requires PHP 8.2+)
2. Check Composer: `composer --version`
3. Check Laravel version: `php artisan --version`
4. Run: `php artisan about` for system info