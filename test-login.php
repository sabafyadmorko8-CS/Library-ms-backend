<?php

// Simple test script to debug login issues
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/');
$response = $kernel->handle($request);

echo "=== LOGIN DEBUG TEST ===\n\n";

try {
    // 1. Check if database exists and has users
    echo "1. Checking database...\n";
    $userCount = \App\Models\User::count();
    echo "   Users in database: $userCount\n";
    
    if ($userCount == 0) {
        echo "   ❌ NO USERS FOUND! Creating test user...\n";
        
        $user = \App\Models\User::create([
            'name' => 'Sabaf (Test)',
            'email' => 'admin@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin'
        ]);
        
        echo "   ✅ Created user: {$user->email}\n";
    }
    
    // 2. List all users
    echo "\n2. All users in database:\n";
    $users = \App\Models\User::all();
    foreach ($users as $user) {
        echo "   - {$user->email} ({$user->role}) - Created: {$user->created_at}\n";
    }
    
    // 3. Test login with each user
    echo "\n3. Testing login for each user with password 'password':\n";
    foreach ($users as $user) {
        $isValid = \Illuminate\Support\Facades\Hash::check('password', $user->password);
        $status = $isValid ? "✅ VALID" : "❌ INVALID";
        echo "   - {$user->email}: $status\n";
        
        if ($isValid) {
            echo "     → Use this email: {$user->email}\n";
            echo "     → Use this password: password\n";
        }
    }
    
    // 4. Test the actual login API
    echo "\n4. Testing login API directly:\n";
    $testEmail = $users->first()->email ?? 'admin@test.com';
    
    // Simulate the login request
    $loginRequest = new \Illuminate\Http\Request();
    $loginRequest->merge([
        'email' => $testEmail,
        'password' => 'password'
    ]);
    
    $authController = new \App\Http\Controllers\Api\AuthController();
    
    try {
        $result = $authController->login($loginRequest);
        echo "   ✅ API Login successful!\n";
        echo "   Response: " . $result->getContent() . "\n";
    } catch (Exception $e) {
        echo "   ❌ API Login failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Make sure Laravel server is running: php artisan serve\n";
echo "2. Test with browser: http://localhost:8000/api/debug-users\n";
echo "3. Use the working email from above in your frontend\n";
echo "4. If still failing, check browser network tab for actual error\n";