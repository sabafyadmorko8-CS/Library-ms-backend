<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel backend is working!',
        'timestamp' => now(),
        'status' => 'OK'
    ]);
});

// Test route to check if Laravel is working
Route::get('/test', function () {
    return response()->json([
        'message' => 'Laravel backend is working!',
        'timestamp' => now(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version()
    ]);
});