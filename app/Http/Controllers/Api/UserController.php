<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only)
     */
    public function index(): JsonResponse
    {
        $users = User::with('borrows.book')->get();
        return response()->json($users);
    }

    /**
     * Store a newly created user (Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,librarian,user'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with('borrows.book')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified user (Admin only)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:admin,librarian,user'
        ]);

        $updateData = $request->only(['name', 'email', 'role']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        return response()->json($user);
    }

    /**
     * Remove the specified user (Admin only)
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            // Check if user has active borrows
            $activeBorrows = $user->borrows()->where('status', 'borrowed')->count();
            if ($activeBorrows > 0) {
                return response()->json([
                    'error' => 'Cannot delete user with active borrows',
                    'active_borrows' => $activeBorrows
                ], 400);
            }

            $userName = $user->name;
            $user->delete();
            
            return response()->json([
                'message' => "User '{$userName}' deleted successfully"
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's borrowing history
     */
    public function myBorrows(Request $request): JsonResponse
    {
        // For now, return empty array since we don't have auth
        // TODO: Implement when Sanctum is added
        return response()->json([]);
    }
}