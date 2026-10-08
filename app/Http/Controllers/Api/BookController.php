<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * Display a listing of books (All users can view)
     */
    public function index(): JsonResponse
    {
        try {
            // Try with borrows relationship, fall back to plain query if borrows table missing
            try {
                $books = Book::with('borrows')->get();
            } catch (\Exception $e) {
                $books = Book::all();
            }
            return response()->json($books);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch books',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created book (Admin & Librarian only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'required|string|unique:books,isbn|max:20',
                'year' => 'required|integer|min:1000|max:' . (date('Y') + 10),
                'quantity' => 'required|integer|min:0'
            ]);

            $book = Book::create($request->all());
            return response()->json([
                'message' => 'Book created successfully',
                'book' => $book
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create book',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified book
     */
    public function show(string $id): JsonResponse
    {
        $book = Book::with('borrows.user')->findOrFail($id);
        return response()->json($book);
    }

    /**
     * Update the specified book (Admin & Librarian only)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $book = Book::findOrFail($id);
            
            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'author' => 'sometimes|required|string|max:255',
                'isbn' => 'sometimes|required|string|unique:books,isbn,' . $id . '|max:20',
                'year' => 'sometimes|required|integer|min:1000|max:' . (date('Y') + 10),
                'quantity' => 'sometimes|required|integer|min:0'
            ]);

            $book->update($request->all());
            return response()->json([
                'message' => 'Book updated successfully',
                'book' => $book
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Book not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update book',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified book (Admin only)
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $book = Book::findOrFail($id);
            
            // Check if book has active borrows
            $activeBorrows = $book->borrows()->where('status', 'borrowed')->count();
            if ($activeBorrows > 0) {
                return response()->json([
                    'error' => 'Cannot delete book with active borrows',
                    'active_borrows' => $activeBorrows
                ], 400);
            }

            $bookTitle = $book->title;
            $book->delete();
            
            return response()->json([
                'message' => "Book '{$bookTitle}' deleted successfully"
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Book not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete book',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available books for borrowing
     */
    public function available(): JsonResponse
    {
        $books = Book::where('quantity', '>', 0)->get();
        return response()->json($books);
    }
}
