# 🔍 Step-by-Step Login Debug Guide

## Step 1: Run the Test Script
```bash
cd backend-laravel
php test-login.php
```
This will show you exactly what users exist and which passwords work.

## Step 2: Test with HTML Page
1. Start Laravel server: `php artisan serve`
2. Open browser: `http://localhost:8000/test-login.html`
3. Click "Check Users" to see what users exist
4. Click "Test API" to verify API is working
5. Try login with the credentials shown

## Step 3: Check Database Manually
```bash
php artisan tinker
```
Then run:
```php
// Check users
User::all(['email', 'role'])->toArray()

// Test password for specific user
$user = User::where('email', 'admin@library.com')->first();
Hash::check('password', $user->password)

// Create new user if needed
User::create(['name' => 'Debug Admin', 'email' => 'debug@admin.com', 'password' => Hash::make('password'), 'role' => 'admin']);
```

## Step 4: Reset Everything (Nuclear Option)
```bash
# Delete database and start fresh
rm database/database.sqlite
touch database/database.sqlite

# Run migrations and seed
php artisan migrate:fresh --seed

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Start server
php artisan serve
```

## Step 5: Test Frontend Connection
Open browser developer tools (F12) and check:
1. Network tab - see what requests are being made
2. Console tab - check for JavaScript errors
3. Look for CORS errors or 500 server errors

## Step 6: Manual API Test with curl
```bash
# Test API is working
curl http://localhost:8000/api/test

# Test user list
curl http://localhost:8000/api/debug-users

# Test login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@library.com","password":"password"}'
```

## Common Issues & Solutions

### Issue: "Connection refused"
- Laravel server not running
- **Solution:** `php artisan serve`

### Issue: "User not found"
- Database not seeded
- **Solution:** `php artisan migrate:fresh --seed`

### Issue: "Invalid password"
- Password hash mismatch
- **Solution:** Create new user with known password

### Issue: "CORS error"
- Frontend can't connect to backend
- **Solution:** Check CORS config, make sure both servers running

### Issue: "500 Server Error"
- PHP/Laravel error
- **Solution:** Check `storage/logs/laravel.log`

## Working Credentials (after seeding)
- admin@library.com / password
- librarian@library.com / password  
- alice@student.com / password

## Next Steps
1. Run Step 1 and 2 above
2. Share the output with me
3. We'll identify the exact issue and fix it