<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'sometimes|in:admin,librarian,user'
            ]);

            // Check if status column exists (migration has been run)
            $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status');
            
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user'
            ];
            
            // Add status if column exists
            if ($hasStatusColumn) {
                $userData['status'] = 'pending';
            }

            $user = User::create($userData);

            if ($hasStatusColumn) {
                return response()->json([
                    'message' => 'Registration successful! Your account is pending admin approval.',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status ?? 'approved'
                    ]
                ], 201);
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
                    'note' => 'Run "php artisan migrate" to enable admin verification system'
                ], 201);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
                'debug' => [
                    'has_status_column' => \Illuminate\Support\Facades\Schema::hasColumn('users', 'status'),
                    'table_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('users')
                ]
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if status column exists and user is approved
            $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status');
            
            if ($hasStatusColumn && isset($user->status) && $user->status !== 'approved') {
                $message = match($user->status) {
                    'pending' => 'Your account is pending admin approval.',
                    'rejected' => 'Your account has been rejected by an admin.',
                    default => 'Your account is not active.'
                };
                
                return response()->json([
                    'message' => $message,
                    'status' => $user->status
                ], 403);
            }

            // Create a simple token for now (will be replaced with Sanctum later)
            $token = 'temp_token_' . $user->id . '_' . time();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status ?? 'approved'
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        // For now, just return success (will be implemented with Sanctum later)
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        // For now, return empty (will be implemented with Sanctum later)
        return response()->json(['message' => 'Authentication not implemented yet']);
    }

    /**
     * Get pending users (admin only)
     */
    public function getPendingUsers(): JsonResponse
    {
        try {
            // Check if status column exists
            $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status');
            
            if (!$hasStatusColumn) {
                return response()->json([
                    'message' => 'User verification system not enabled. Run "php artisan migrate" first.',
                    'pending_users' => [],
                    'migration_needed' => true
                ]);
            }
            
            $pendingUsers = User::where('status', 'pending')
                ->select('id', 'name', 'email', 'role', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'pending_users' => $pendingUsers,
                'total' => $pendingUsers->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch pending users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve user (admin only)
     */
    public function approveUser(Request $request, $userId): JsonResponse
    {
        try {
            // Check if status column exists
            $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status');
            
            if (!$hasStatusColumn) {
                return response()->json([
                    'message' => 'User verification system not enabled. Run "php artisan migrate" first.',
                    'migration_needed' => true
                ], 400);
            }
            
            $user = User::findOrFail($userId);
            
            if ($user->status !== 'pending') {
                return response()->json([
                    'message' => 'User is not in pending status. Current status: ' . $user->status
                ], 400);
            }

            // Find the first admin user to use as approved_by
            $adminUser = User::where('role', 'admin')->first();
            $approvedBy = $adminUser ? $adminUser->id : null;

            $user->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $approvedBy
            ]);

            return response()->json([
                'message' => 'User approved successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ],
                'approved_by_admin' => $adminUser ? $adminUser->name : 'System'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject user (admin only)
     */
    public function rejectUser(Request $request, $userId): JsonResponse
    {
        try {
            // Check if status column exists
            $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status');
            
            if (!$hasStatusColumn) {
                return response()->json([
                    'message' => 'User verification system not enabled. Run "php artisan migrate" first.',
                    'migration_needed' => true
                ], 400);
            }
            
            $user = User::findOrFail($userId);
            
            if ($user->status !== 'pending') {
                return response()->json([
                    'message' => 'User is not in pending status. Current status: ' . $user->status
                ], 400);
            }

            // Find the first admin user to use as approved_by
            $adminUser = User::where('role', 'admin')->first();
            $approvedBy = $adminUser ? $adminUser->id : null;

            $user->update([
                'status' => 'rejected',
                'approved_by' => $approvedBy
            ]);

            return response()->json([
                'message' => 'User rejected successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ],
                'rejected_by_admin' => $adminUser ? $adminUser->name : 'System'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}