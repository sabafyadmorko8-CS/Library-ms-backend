<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BorrowController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReportController;

// Test route
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Debug route to check users
Route::get('/debug-users', function () {
    try {
        $users = \App\Models\User::all(['id', 'name', 'email', 'role', 'created_at']);
        return response()->json([
            'total_users' => $users->count(),
            'users' => $users,
            'admin_exists' => \App\Models\User::where('email', 'admin@library.com')->exists(),
            'test_credentials' => [
                'email' => 'admin@library.com',
                'password' => 'password'
            ]
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Database error: ' . $e->getMessage(),
            'suggestion' => 'Run: php artisan migrate:fresh --seed'
        ], 500);
    }
});

// Test login route
Route::post('/test-login', function (\Illuminate\Http\Request $request) {
    $email = $request->input('email', 'admin@library.com');
    $password = $request->input('password', 'password');
    
    // Debug info
    $debugInfo = [
        'input_email' => $email,
        'input_password' => $password,
        'all_users' => \App\Models\User::pluck('email')->toArray()
    ];
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json([
            'error' => 'User not found',
            'debug' => $debugInfo
        ], 404);
    }
    
    $passwordValid = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    
    // Test with different passwords
    $testPasswords = ['password', 'admin', '123456', 'secret'];
    $passwordTests = [];
    foreach ($testPasswords as $testPwd) {
        $passwordTests[$testPwd] = \Illuminate\Support\Facades\Hash::check($testPwd, $user->password);
    }
    
    return response()->json([
        'user_found' => true,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at
        ],
        'password_valid' => $passwordValid,
        'password_hash' => substr($user->password, 0, 30) . '...',
        'password_tests' => $passwordTests,
        'debug' => $debugInfo
    ]);
});

// Create test user route
Route::get('/create-test-user', function () {
    try {
        // Delete existing admin user if exists
        \App\Models\User::where('email', 'sabaf@admin.com')->delete();
        
        // Create new admin user
        $user = \App\Models\User::create([
            'name' => 'Sabaf',
            'email' => 'sabaf@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin'
        ]);
        
        // Verify the password works
        $passwordCheck = \Illuminate\Support\Facades\Hash::check('password', $user->password);
        
        return response()->json([
            'message' => 'Test user created successfully!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'password_verification' => $passwordCheck,
            'instructions' => 'Now try logging in with: sabaf@admin.com / password'
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to create user: ' . $e->getMessage()
        ], 500);
    }
});

// Debug login route
Route::post('/debug-login', function (\Illuminate\Http\Request $request) {
    try {
        $email = $request->input('email', 'sabaf@admin.com');
        $password = $request->input('password', 'password');
        
        // Check if user exists
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'email' => $email,
                'all_users' => \App\Models\User::pluck('email')->toArray()
            ], 404);
        }
        
        // Check password
        $passwordValid = \Illuminate\Support\Facades\Hash::check($password, $user->password);
        
        if (!$passwordValid) {
            return response()->json([
                'error' => 'Invalid password',
                'user_found' => true,
                'password_hash_preview' => substr($user->password, 0, 20) . '...'
            ], 401);
        }
        
        // Success - return user data
        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => 'temp_token_' . $user->id . '_' . time()
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Debug login failed: ' . $e->getMessage()
        ], 500);
    }
});

// Test user management routes
Route::get('/test-user-management', function () {
    try {
        // Test creating a user
        $testUser = \App\Models\User::create([
            'name' => 'Test User Management',
            'email' => 'test-user-mgmt@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'user'
        ]);
        
        // Test updating the user
        $testUser->update(['name' => 'Updated Test User']);
        
        // Test deleting the user
        $testUser->delete();
        
        return response()->json([
            'message' => 'User management endpoints are working!',
            'operations_tested' => ['create', 'update', 'delete'],
            'status' => 'success'
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'User management test failed: ' . $e->getMessage()
        ], 500);
    }
});

// Create demo books route
Route::get('/create-demo-books', function () {
    try {
        $books = [];
        
        // Create sample books
        $sampleBooks = [
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '978-0-7432-7356-5',
                'year' => 1925,
                'quantity' => 5
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'isbn' => '978-0-06-112008-4',
                'year' => 1960,
                'quantity' => 3
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '978-0-452-28423-4',
                'year' => 1949,
                'quantity' => 4
            ],
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'isbn' => '978-0-14-143951-8',
                'year' => 1813,
                'quantity' => 2
            ],
            [
                'title' => 'The Catcher in the Rye',
                'author' => 'J.D. Salinger',
                'isbn' => '978-0-316-76948-0',
                'year' => 1951,
                'quantity' => 3
            ]
        ];
        
        foreach ($sampleBooks as $bookData) {
            $book = \App\Models\Book::updateOrCreate(
                ['isbn' => $bookData['isbn']],
                $bookData
            );
            $books[] = $book;
        }
        
        return response()->json([
            'message' => 'Demo books created successfully!',
            'books' => $books,
            'total_books' => count($books)
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to create demo books: ' . $e->getMessage()
        ], 500);
    }
});

// Create all demo users route
Route::get('/create-demo-users', function () {
    try {
        $users = [];
        
        // Create Sabaf user (main)
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@library.com'],
            [
                'name' => 'Sabaf',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin'
            ]
        );
        $users[] = $admin;
        
        // Create Librarian user
        $librarian = \App\Models\User::updateOrCreate(
            ['email' => 'librarian@library.com'],
            [
                'name' => ' Librarian',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'librarian'
            ]
        );
        $users[] = $librarian;
        
        // Create Regular User (matching the demo credentials)
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'alice@student.com'],
            [
                'name' => ' Student',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'user'
            ]
        );
        $users[] = $user;
        
        // Create additional user
        $user2 = \App\Models\User::updateOrCreate(
            ['email' => 'user@library.com'],
            [
                'name' => ' User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'user'
            ]
        );
        $users[] = $user2;
        
        // Keep the working admin
        $testAdmin = \App\Models\User::updateOrCreate(
            ['email' => 'sabaf@admin.com'],
            [
                'name' => 'Sabaf',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin'
            ]
        );
        $users[] = $testAdmin;
        
        return response()->json([
            'message' => 'All demo users created successfully!',
            'users' => array_map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role
                ];
            }, $users)
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to create users: ' . $e->getMessage()
        ], 500);
    }
});

// Test registration route
Route::post('/test-register', function (\Illuminate\Http\Request $request) {
    try {
        // Check if status column exists
        $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status');
        
        $debugInfo = [
            'has_status_column' => $hasStatusColumn,
            'request_data' => $request->all(),
            'table_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('users'),
            'validation_rules' => [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'sometimes|in:admin,librarian,user'
            ]
        ];
        
        // Check validation first
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'sometimes|in:admin,librarian,user'
            ]);
            $debugInfo['validation'] = 'PASSED';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $debugInfo['validation'] = 'FAILED';
            $debugInfo['validation_errors'] = $e->errors();
            
            return response()->json([
                'error' => 'Validation failed',
                'validation_errors' => $e->errors(),
                'debug' => $debugInfo
            ], 422);
        }
        
        // Check if email already exists
        $existingUser = \App\Models\User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'error' => 'Email already exists',
                'existing_user' => [
                    'id' => $existingUser->id,
                    'name' => $existingUser->name,
                    'email' => $existingUser->email,
                    'role' => $existingUser->role
                ],
                'debug' => $debugInfo
            ], 422);
        }
        
        // Prepare user data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role ?? 'user'
        ];
        
        // Add status if column exists
        if ($hasStatusColumn) {
            $userData['status'] = 'pending';
        }
        
        $debugInfo['user_data_to_create'] = array_merge($userData, ['password' => '[HIDDEN]']);
        
        // Try to create user without status first
        try {
            $user = \App\Models\User::create($userData);
            $debugInfo['user_creation'] = 'SUCCESS';
            
            if ($hasStatusColumn) {
                return response()->json([
                    'message' => 'Registration successful! Your account is pending admin approval.',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status ?? 'pending'
                    ],
                    'debug' => $debugInfo
                ]);
            } else {
                // Old behavior - create token immediately
                $token = 'temp_token_' . $user->id . '_' . time();
                
                return response()->json([
                    'message' => 'Registration successful!',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'token' => $token,
                    'note' => 'Run "php artisan migrate" to enable admin verification system',
                    'debug' => $debugInfo
                ]);
            }
            
        } catch (\Exception $e) {
            $debugInfo['user_creation'] = 'FAILED';
            $debugInfo['creation_error'] = $e->getMessage();
            $debugInfo['creation_trace'] = $e->getTraceAsString();
            
            return response()->json([
                'error' => 'User creation failed: ' . $e->getMessage(),
                'debug' => $debugInfo
            ], 500);
        }
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Registration failed: ' . $e->getMessage(),
            'debug' => [
                'has_status_column' => \Illuminate\Support\Facades\Schema::hasColumn('users', 'status'),
                'table_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('users'),
                'trace' => $e->getTraceAsString()
            ]
        ], 500);
    }
});

// Quick database check route
Route::get('/check-registration-setup', function () {
    try {
        $checks = [
            'users_table_exists' => \Illuminate\Support\Facades\Schema::hasTable('users'),
            'users_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('users'),
            'has_status_column' => \Illuminate\Support\Facades\Schema::hasColumn('users', 'status'),
            'total_users' => \App\Models\User::count(),
            'sample_user' => \App\Models\User::first(['id', 'name', 'email', 'role']),
            'fillable_fields' => (new \App\Models\User())->getFillable()
        ];
        
        return response()->json([
            'message' => 'Registration setup check completed',
            'checks' => $checks,
            'ready_for_registration' => $checks['users_table_exists'] && count($checks['users_columns']) > 0
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Setup check failed: ' . $e->getMessage()
        ], 500);
    }
});

// 🔐 Authentication routes (public) - TEMPORARILY WITHOUT AUTH
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// User verification routes (admin only)
Route::get('/pending-users', [AuthController::class, 'getPendingUsers']);
Route::post('/approve-user/{userId}', [AuthController::class, 'approveUser']);
Route::post('/reject-user/{userId}', [AuthController::class, 'rejectUser']);

// Debug routes for approval system
Route::get('/debug-approval-system', function () {
    try {
        // Check if status column exists
        $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status');
        $userColumns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
        
        // Get sample data
        $totalUsers = \App\Models\User::count();
        $pendingUsers = $hasStatusColumn ? \App\Models\User::where('status', 'pending')->count() : 0;
        $approvedUsers = $hasStatusColumn ? \App\Models\User::where('status', 'approved')->count() : $totalUsers;
        
        // Get sample pending user
        $samplePendingUser = $hasStatusColumn ? \App\Models\User::where('status', 'pending')->first() : null;
        
        // Check for admin users
        $adminUsers = \App\Models\User::where('role', 'admin')->get(['id', 'name', 'email']);
        
        return response()->json([
            'system_status' => 'working',
            'has_status_column' => $hasStatusColumn,
            'user_columns' => $userColumns,
            'statistics' => [
                'total_users' => $totalUsers,
                'pending_users' => $pendingUsers,
                'approved_users' => $approvedUsers,
                'admin_users' => $adminUsers->count()
            ],
            'admin_users' => $adminUsers,
            'sample_pending_user' => $samplePendingUser,
            'endpoints' => [
                'pending_users' => '/api/pending-users',
                'approve_user' => '/api/approve-user/{id}',
                'reject_user' => '/api/reject-user/{id}'
            ],
            'migration_needed' => !$hasStatusColumn
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Debug failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Create admin user if none exists
Route::get('/ensure-admin-exists', function () {
    try {
        $adminCount = \App\Models\User::where('role', 'admin')->count();
        
        if ($adminCount === 0) {
            // Create an admin user
            $admin = \App\Models\User::create([
                'name' => 'System Admin',
                'email' => 'admin@library.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
                'status' => 'approved' // Admin is automatically approved
            ]);
            
            return response()->json([
                'message' => 'Admin user created successfully!',
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => $admin->role
                ],
                'login_credentials' => [
                    'email' => 'admin@library.com',
                    'password' => 'password'
                ]
            ]);
        } else {
            $admins = \App\Models\User::where('role', 'admin')->get(['id', 'name', 'email']);
            return response()->json([
                'message' => 'Admin users already exist',
                'admin_count' => $adminCount,
                'admins' => $admins
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to create admin: ' . $e->getMessage()
        ], 500);
    }
});

// Add GET route for browser testing (TEMPORARY)
Route::get('/login', function () {
    return response()->json([
        'message' => 'Login endpoint is working!',
        'note' => 'Use POST method to actually login',
        'test_credentials' => [
            'email' => 'sabaf@admin.com',
            'password' => 'password'
        ],
        'test_page' => 'http://127.0.0.1:8000/quick-test.html'
    ]);
});

// Add a simple test login route that bypasses any middleware issues
Route::post('/simple-login', function (\Illuminate\Http\Request $request) {
    try {
        $email = $request->input('email');
        $password = $request->input('password');
        
        if (!$email || !$password) {
            return response()->json([
                'error' => 'Email and password are required'
            ], 400);
        }
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'debug_info' => [
                    'searched_email' => $email,
                    'available_users' => \App\Models\User::pluck('email')->toArray(),
                    'total_users' => \App\Models\User::count()
                ]
            ], 404);
        }
        
        // Test password with detailed debugging
        $passwordValid = \Illuminate\Support\Facades\Hash::check($password, $user->password);
        
        if (!$passwordValid) {
            // Test with common passwords for debugging
            $testPasswords = ['password', 'admin', '123456', 'secret'];
            $passwordTests = [];
            foreach ($testPasswords as $testPwd) {
                $passwordTests[$testPwd] = \Illuminate\Support\Facades\Hash::check($testPwd, $user->password);
            }
            
            return response()->json([
                'error' => 'Invalid password',
                'debug_info' => [
                    'user_found' => true,
                    'user_email' => $user->email,
                    'password_hash_preview' => substr($user->password, 0, 30) . '...',
                    'password_tests' => $passwordTests,
                    'suggestion' => 'Try visiting /api/force-create-admin to reset admin user'
                ]
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => 'temp_token_' . $user->id . '_' . time(),
            'message' => 'Login successful!'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Login failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Basic routes without authentication for testing
Route::get('/books', [BookController::class, 'index']);

// Create Sabaf admin user
Route::get('/create-sabaf-admin', function () {
    try {
        // Delete any existing user with this email
        \App\Models\User::where('email', 'sabaf@admin.com')->delete();
        
        // Create Sabaf admin user
        $admin = \App\Models\User::create([
            'name' => 'Sabaf',
            'email' => 'sabaf@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin'
        ]);
        
        // Verify the password works
        $passwordCheck = \Illuminate\Support\Facades\Hash::check('password', $admin->password);
        
        return response()->json([
            'message' => 'Sabaf admin user created successfully!',
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role
            ],
            'password_verification' => $passwordCheck ? 'PASSED' : 'FAILED',
            'login_credentials' => [
                'email' => 'sabaf@admin.com',
                'password' => 'password'
            ]
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to create Sabaf admin: ' . $e->getMessage()
        ], 500);
    }
});

// Create MySQL database route
Route::get('/create-mysql-database', function () {
    try {
        // Try to connect to MySQL without specifying database
        $config = config('database.connections.mysql');
        $config['database'] = null; // Remove database from connection
        
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}",
            $config['username'],
            $config['password'],
            $config['options'] ?? []
        );
        
        // Create database
        $databaseName = 'Library_ms';
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Check if database was created
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$databaseName}'");
        $exists = $stmt->fetch() !== false;
        
        if ($exists) {
            return response()->json([
                'message' => 'MySQL database created successfully!',
                'database' => $databaseName,
                'host' => $config['host'],
                'port' => $config['port'],
                'next_steps' => [
                    '1. Run migrations: php artisan migrate:fresh',
                    '2. Create demo data: visit /api/auto-setup',
                    '3. Test your app!'
                ]
            ]);
        } else {
            return response()->json([
                'error' => 'Database creation failed - database not found after creation'
            ], 500);
        }
        
    } catch (\PDOException $e) {
        return response()->json([
            'error' => 'MySQL connection failed: ' . $e->getMessage(),
            'suggestions' => [
                'Make sure MySQL is running (XAMPP/WAMP)',
                'Check if port 3306 is available',
                'Verify MySQL username/password in .env file',
                'Ensure MySQL user has CREATE DATABASE privileges'
            ]
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Database creation failed: ' . $e->getMessage()
        ], 500);
    }
});

// Auto-setup route - creates users and books if they don't exist
Route::get('/auto-setup', function () {
    try {
        $results = [];
        
        // Check and create users if needed
        $userCount = \App\Models\User::count();
        if ($userCount === 0) {
            // Create demo users
            $users = [
                [
                    'name' => 'Sabaf',
                    'email' => 'sabaf@admin.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role' => 'admin'
                ],
                [
                    'name' => 'libr Librarian',
                    'email' => 'librarian@library.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role' => 'librarian'
                ],
                [
                    'name' => 'Alice Student',
                    'email' => 'user@library.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role' => 'user'
                ]
            ];
            
            foreach ($users as $userData) {
                \App\Models\User::create($userData);
            }
            $results['users'] = 'Created 3 demo users';
        } else {
            $results['users'] = "Found {$userCount} existing users";
        }
        
        // Check and create books if needed
        $bookCount = \App\Models\Book::count();
        if ($bookCount === 0) {
            $books = [
                [
                    'title' => 'The Great Gatsby',
                    'author' => 'F. Scott Fitzgerald',
                    'isbn' => '978-0-7432-7356-5',
                    'year' => 1925,
                    'quantity' => 5
                ],
                [
                    'title' => '1984',
                    'author' => 'George Orwell',
                    'isbn' => '978-0-452-28423-4',
                    'year' => 1949,
                    'quantity' => 3
                ]
            ];
            
            foreach ($books as $bookData) {
                \App\Models\Book::create($bookData);
            }
            $results['books'] = 'Created 2 demo books';
        } else {
            $results['books'] = "Found {$bookCount} existing books";
        }
        
        // Get current state
        $currentUsers = \App\Models\User::select('id', 'name', 'email', 'role')->get();
        $currentBooks = \App\Models\Book::select('id', 'title', 'author', 'quantity')->get();
        
        return response()->json([
            'message' => 'Auto-setup completed!',
            'results' => $results,
            'current_users' => $currentUsers,
            'current_books' => $currentBooks,
            'ready_to_test' => true
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Auto-setup failed: ' . $e->getMessage()
        ], 500);
    }
});

// Fix database - run migrations
Route::get('/fix-database', function () {
    try {
        // Run migrations
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = \Illuminate\Support\Facades\Artisan::output();
        
        // Check if books table exists and has correct columns
        $tableExists = \Illuminate\Support\Facades\Schema::hasTable('books');
        $columns = $tableExists ? \Illuminate\Support\Facades\Schema::getColumnListing('books') : [];
        
        // Check if users table exists
        $usersTableExists = \Illuminate\Support\Facades\Schema::hasTable('users');
        
        return response()->json([
            'message' => 'Database migration completed!',
            'migration_output' => $migrationOutput,
            'books_table_exists' => $tableExists,
            'books_columns' => $columns,
            'users_table_exists' => $usersTableExists,
            'required_columns' => ['id', 'title', 'author', 'isbn', 'quantity', 'created_at', 'updated_at'],
            'missing_columns' => array_diff(['id', 'title', 'author', 'isbn', 'quantity', 'created_at', 'updated_at'], $columns)
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Migration failed: ' . $e->getMessage(),
            'suggestion' => 'Try running: php artisan migrate --force'
        ], 500);
    }
});

// Force create working admin user
Route::get('/force-create-admin', function () {
    try {
        // Delete any existing admin
        \App\Models\User::where('email', 'sabaf@admin.com')->delete();
        
        // Create fresh admin user
        $admin = \App\Models\User::create([
            'name' => 'Sabaf',
            'email' => 'sabaf@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin'
        ]);
        
        // Verify password immediately
        $passwordCheck = \Illuminate\Support\Facades\Hash::check('password', $admin->password);
        
        return response()->json([
            'message' => 'Sabaf user force-created successfully!',
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role
            ],
            'password_hash' => substr($admin->password, 0, 30) . '...',
            'password_verification' => $passwordCheck ? 'PASSED' : 'FAILED',
            'login_credentials' => [
                'email' => 'sabaf@admin.com',
                'password' => 'password'
            ]
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to create admin: ' . $e->getMessage()
        ], 500);
    }
});

// Test database connection and books table
Route::get('/test-books-table', function () {
    try {
        // Test if books table exists
        $tableExists = \Illuminate\Support\Facades\Schema::hasTable('books');
        
        if (!$tableExists) {
            return response()->json([
                'error' => 'Books table does not exist',
                'suggestion' => 'Run: php artisan migrate'
            ], 500);
        }
        
        // Test if we can query books
        $bookCount = \App\Models\Book::count();
        $books = \App\Models\Book::take(3)->get();
        
        return response()->json([
            'table_exists' => true,
            'book_count' => $bookCount,
            'sample_books' => $books,
            'table_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('books')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Database test failed: ' . $e->getMessage()
        ], 500);
    }
});

// Debug book creation route
Route::post('/debug-books', function (\Illuminate\Http\Request $request) {
    try {
        $data = $request->all();
        
        // Log what we received
        $debugInfo = [
            'received_data' => $data,
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
            'validation_rules' => [
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'required|string|unique:books,isbn|max:20',
                'year' => 'required|integer|min:1000|max:' . (date('Y') + 10),
                'quantity' => 'required|integer|min:0'
            ]
        ];
        
        // Try validation
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'required|string|unique:books,isbn|max:20',
                'year' => 'required|integer|min:1000|max:' . (date('Y') + 10),
                'quantity' => 'required|integer|min:0'
            ]);
            $debugInfo['validation'] = 'PASSED';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $debugInfo['validation'] = 'FAILED';
            $debugInfo['validation_errors'] = $e->errors();
        }
        
        // Try creating book
        try {
            $book = \App\Models\Book::create($data);
            $debugInfo['book_creation'] = 'SUCCESS';
            $debugInfo['created_book'] = $book;
        } catch (\Exception $e) {
            $debugInfo['book_creation'] = 'FAILED';
            $debugInfo['creation_error'] = $e->getMessage();
        }
        
        return response()->json($debugInfo);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Debug failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// 📚 Book management routes (temporarily without auth for testing)
Route::get('/books/available', [BookController::class, 'available']);
Route::post('/books', [BookController::class, 'store']);
Route::get('/books/{id}', [BookController::class, 'show']);
Route::put('/books/{id}', [BookController::class, 'update']);
Route::delete('/books/{id}', [BookController::class, 'destroy']);

// 👥 User management routes (temporarily without auth for testing)
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// 📊 Reports routes (temporarily without auth for testing)
Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
Route::get('/reports/popular-books', [ReportController::class, 'popularBooks']);
Route::get('/reports/user-stats', [ReportController::class, 'userStats']);
Route::get('/reports/overdue', [ReportController::class, 'overdueBooks']);
Route::get('/reports/trends', [ReportController::class, 'monthlyTrends']);

// 🔄 Borrow routes (temporarily without auth for testing)
Route::get('/borrows', [BorrowController::class, 'index']);
Route::post('/borrows', [BorrowController::class, 'store']);
Route::get('/borrows/{id}', [BorrowController::class, 'show']);
Route::put('/borrows/{id}', [BorrowController::class, 'update']);
Route::delete('/borrows/{id}', [BorrowController::class, 'destroy']);
Route::put('/borrows/{id}/return', [BorrowController::class, 'returnBook']);
Route::get('/my-borrows', [BorrowController::class, 'myBorrows']);

// Fallback route for my-borrows without user_id (for debugging)
Route::get('/my-borrows-fallback', function () {
    return response()->json([
        'error' => 'User ID is required',
        'message' => 'Please use /my-borrows?user_id=YOUR_USER_ID',
        'available_users' => \App\Models\User::select('id', 'name', 'email')->get(),
        'example' => '/api/my-borrows?user_id=1'
    ], 400);
});

// Debug route to check user borrows
Route::get('/debug-user-borrows/{userId}', function ($userId) {
    try {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'available_users' => \App\Models\User::select('id', 'name', 'email')->get()
            ], 404);
        }
        
        $borrows = \App\Models\Borrow::with(['user', 'book'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'user' => $user,
            'total_borrows' => $borrows->count(),
            'active_borrows' => $borrows->where('status', 'borrowed')->count(),
            'returned_borrows' => $borrows->where('status', 'returned')->count(),
            'borrows' => $borrows
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Debug failed: ' . $e->getMessage()
        ], 500);
    }
});

// We'll add back authentication after Sanctum is installed