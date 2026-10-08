<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BorrowController extends Controller
{
    /**
     * Display a listing of borrows (Admin & Librarian only)
     */
    public function index(): JsonResponse
    {
        $borrows = Borrow::with(['user', 'book'])->get();
        return response()->json($borrows);
    }

    /**
     * Store a newly created borrow (Users can borrow books)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'book_id' => 'required|exists:books,id',
                'user_id' => 'sometimes|integer|min:1' // Don't validate exists yet, we'll handle it manually
            ]);

            $book = Book::findOrFail($request->book_id);
            
            // For now, use provided user_id or default to first available user
            $userId = $request->user_id ?? null;
            
            // If no user_id provided, try to find the first user
            if (!$userId) {
                $firstUser = \App\Models\User::first();
                if (!$firstUser) {
                    return response()->json([
                        'error' => 'No users found in database',
                        'suggestion' => 'Please create demo users first',
                        'create_users_url' => '/api/create-demo-users'
                    ], 404);
                }
                $userId = $firstUser->id;
            }
            
            // Try to find the user
            $user = \App\Models\User::find($userId);
            if (!$user) {
                // Get available user IDs for debugging
                $availableUsers = \App\Models\User::select('id', 'name', 'email')->get();
                
                return response()->json([
                    'error' => "User with ID {$userId} not found",
                    'available_users' => $availableUsers,
                    'suggestion' => 'Use one of the available user IDs or create demo users',
                    'create_users_url' => '/api/create-demo-users'
                ], 404);
            }

            // Check if user is approved (if status column exists)
            try {
                if (\Schema::hasColumn('users', 'status')) {
                    if ($user->status !== 'approved') {
                        return response()->json([
                            'error' => 'Account not approved',
                            'message' => 'Your account must be approved by an admin before you can borrow books',
                            'status' => $user->status
                        ], 403);
                    }
                }
            } catch (\Exception $e) {
                // If status column doesn't exist, continue without check
            }

            if ($book->quantity < 1) {
                return response()->json([
                    'error' => 'Book not available - no copies left'
                ], 400);
            }

            // Check if user already has this book borrowed
            $existingBorrow = Borrow::where('book_id', $request->book_id)
                ->where('user_id', $userId)
                ->where('status', 'borrowed')
                ->first();

            if ($existingBorrow) {
                return response()->json([
                    'error' => 'You already have this book borrowed'
                ], 400);
            }

            // Check user's active borrow limit (max 3 books)
            $activeBorrows = Borrow::where('user_id', $userId)
                ->where('status', 'borrowed')
                ->count();
                
            if ($activeBorrows >= 3) {
                return response()->json([
                    'error' => 'Maximum borrow limit reached (3 books)'
                ], 400);
            }

            // Reduce book quantity
            $book->decrement('quantity');

            $borrow = Borrow::create([
                'book_id' => $request->book_id,
                'user_id' => $userId,
                'borrowed_at' => now(),
                'status' => 'borrowed'
            ]);

            return response()->json([
                'message' => 'Book borrowed successfully!',
                'borrow' => $borrow->load(['user', 'book'])
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to borrow book: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified borrow
     */
    public function show(string $id): JsonResponse
    {
        $borrow = Borrow::with(['user', 'book'])->findOrFail($id);
        return response()->json($borrow);
    }

    /**
     * Update the specified borrow (Librarian only)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $borrow = Borrow::findOrFail($id);
        
        $request->validate([
            'status' => 'sometimes|required|in:borrowed,returned',
            'returned_at' => 'sometimes|nullable|date'
        ]);

        $borrow->update($request->all());
        return response()->json($borrow->load(['user', 'book']));
    }

    /**
     * Remove the specified borrow (Admin only)
     */
    public function destroy(string $id): JsonResponse
    {
        $borrow = Borrow::findOrFail($id);
        
        // If borrow is active, return the book to inventory
        if ($borrow->status === 'borrowed') {
            $borrow->book->increment('quantity');
        }
        
        $borrow->delete();
        return response()->json(['message' => 'Borrow record deleted successfully']);
    }

    /**
     * Return a book (Librarian can mark as returned)
     */
    public function returnBook(Request $request, string $id): JsonResponse
    {
        $borrow = Borrow::findOrFail($id);

        if ($borrow->status === 'returned') {
            return response()->json(['error' => 'Book already returned'], 400);
        }

        $borrow->update([
            'returned_at' => now(),
            'status' => 'returned'
        ]);

        // Increase book quantity
        $borrow->book->increment('quantity');

        return response()->json($borrow->load(['user', 'book']));
    }

    /**
     * Get pending borrows (Librarian view)
     */
    public function pending(): JsonResponse
    {
        $borrows = Borrow::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->get();
        return response()->json($borrows);
    }

    /**
     * Get user's own borrows
     */
    public function myBorrows(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter
            $userId = $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required',
                    'message' => 'Please provide user_id parameter',
                    'example' => '/api/my-borrows?user_id=1',
                    'create_users_url' => '/api/create-demo-users'
                ], 400);
            }
            
            // Verify user exists
            $user = \App\Models\User::find($userId);
            if (!$user) {
                // Try to get available users for helpful error message
                $availableUsers = \App\Models\User::select('id', 'name', 'email')->get();
                
                return response()->json([
                    'error' => 'User not found',
                    'message' => "User with ID {$userId} does not exist",
                    'available_users' => $availableUsers,
                    'total_users' => $availableUsers->count(),
                    'suggestions' => [
                        'Create demo users: /api/create-demo-users',
                        'Create Sabaf admin: /api/create-sabaf-admin',
                        'Use existing user ID from available_users list'
                    ]
                ], 404);
            }
            
            $borrows = Borrow::with(['user', 'book'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'user' => $user,
                'borrows' => $borrows,
                'total_borrows' => $borrows->count(),
                'active_borrows' => $borrows->where('status', 'borrowed')->count(),
                'returned_borrows' => $borrows->where('status', 'returned')->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch borrows: ' . $e->getMessage(),
                'suggestions' => [
                    'Check if database is connected',
                    'Run migrations: php artisan migrate',
                    'Create demo users: /api/create-demo-users'
                ]
            ], 500);
        }
    }
}