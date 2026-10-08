<?php

echo "=== Laravel Backend Diagnostic ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "Laravel Directory: " . __DIR__ . "\n";

// Check if composer autoload exists
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer autoload found\n";
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    echo "❌ Composer autoload NOT found - run 'composer install'\n";
    exit(1);
}

// Check if .env exists
if (file_exists(__DIR__ . '/.env')) {
    echo "✅ .env file found\n";
} else {
    echo "❌ .env file NOT found\n";
}

// Check if database exists
if (file_exists(__DIR__ . '/database/database.sqlite')) {
    echo "✅ SQLite database found\n";
} else {
    echo "❌ SQLite database NOT found - run migrations\n";
}

// Check if app key is set
$envContent = file_get_contents(__DIR__ . '/.env');
if (strpos($envContent, 'APP_KEY=base64:') !== false) {
    echo "✅ App key is set\n";
} else {
    echo "❌ App key NOT set - run 'php artisan key:generate'\n";
}

// Try to bootstrap Laravel
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "✅ Laravel bootstrap successful\n";
    
    // Try to get Laravel version
    echo "Laravel Version: " . $app->version() . "\n";
    
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Run: composer install\n";
echo "2. Run: php artisan key:generate\n";
echo "3. Run: php artisan migrate:fresh --seed\n";
echo "4. Run: php artisan serve\n";
echo "5. Test: http://localhost:8000/test\n";