<?php

// Simple script to create Sabaf user
// Run this with: php create-sabaf-user.php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    echo "🔧 Creating Sabaf admin user...\n\n";
    
    // Delete existing user if exists
    $existingUser = User::where('email', 'sabaf@admin.com')->first();
    if ($existingUser) {
        $existingUser->delete();
        echo "✅ Deleted existing sabaf@admin.com user\n";
    }
    
    // Create new Sabaf user
    $user = User::create([
        'name' => 'Sabaf',
        'email' => 'sabaf@admin.com',
        'password' => Hash::make('password'),
        'role' => 'admin'
    ]);
    
    echo "✅ Created Sabaf user successfully!\n";
    echo "   ID: {$user->id}\n";
    echo "   Name: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Role: {$user->role}\n\n";
    
    // Test password
    $passwordValid = Hash::check('password', $user->password);
    echo "🔑 Password test: " . ($passwordValid ? "✅ VALID" : "❌ INVALID") . "\n\n";
    
    if ($passwordValid) {
        echo "🎉 SUCCESS! You can now login with:\n";
        echo "   📧 Email: sabaf@admin.com\n";
        echo "   🔑 Password: password\n\n";
        echo "✅ Login should work now!\n";
    } else {
        echo "❌ Password test failed - something went wrong\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nMake sure:\n";
    echo "1. Database is connected\n";
    echo "2. Users table exists (run: php artisan migrate)\n";
    echo "3. MySQL is running\n";
}