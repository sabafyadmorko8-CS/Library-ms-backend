<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a fake request to bootstrap the application
$request = Illuminate\Http\Request::create('/');
$response = $kernel->handle($request);

echo "=== Laravel Authentication Debug ===\n";

try {
    // Check if users exist in database
    $users = \App\Models\User::all();
    echo "Total users in database: " . $users->count() . "\n\n";
    
    if ($users->count() > 0) {
        echo "Users found:\n";
        foreach ($users as $user) {
            echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
        }
        echo "\n";
        
        // Test password verification for Sabaf user
        $adminUser = \App\Models\User::where('email', 'admin@library.com')->first();
        if ($adminUser) {
            echo "Sabaf user found: {$adminUser->name}\n";
            
            // Test password hash
            $testPassword = 'password';
            $isValid = \Illuminate\Support\Facades\Hash::check($testPassword, $adminUser->password);
            echo "Password 'password' is " . ($isValid ? "VALID" : "INVALID") . " for Sabaf user\n";
            
            // Show password hash
            echo "Stored password hash: " . substr($adminUser->password, 0, 20) . "...\n";
            echo "Test hash: " . substr(\Illuminate\Support\Facades\Hash::make($testPassword), 0, 20) . "...\n";
        } else {
            echo "❌ Sabaf user NOT found with email: admin@library.com\n";
        }
        
    } else {
        echo "❌ No users found in database!\n";
        echo "Run: php artisan migrate:fresh --seed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "Make sure to run migrations first: php artisan migrate:fresh --seed\n";
}

echo "\n=== Test Login API ===\n";
echo "Try these credentials:\n";
echo "Email: admin@library.com\n";
echo "Password: password\n";
echo "\nOr create a new user with:\n";
echo "php artisan tinker\n";
echo "User::create(['name' => 'Sabaf', 'email' => 'admin@example.com', 'password' => Hash::make('your_password'), 'role' => 'admin']);\n";