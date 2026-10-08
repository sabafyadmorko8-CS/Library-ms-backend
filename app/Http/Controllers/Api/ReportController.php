<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get dashboard statistics (Admin only)
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_books' => Book::count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_librarians' => User::where('role', 'librarian')->count(),
            'active_borrows' => Borrow::where('status', 'borrowed')->count(),
            'returned_books' => Borrow::where('status', 'returned')->count(),
            'available_books' => Book::where('quantity', '>', 0)->count(),
            'out_of_stock_books' => Book::where('quantity', 0)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get most borrowed books
     */
    public function popularBooks(): JsonResponse
    {
        $popularBooks = Book::withCount('borrows')
            ->orderBy('borrows_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($popularBooks);
    }

    /**
     * Get user borrowing statistics
     */
    public function userStats(): JsonResponse
    {
        $userStats = User::where('role', 'user')
            ->withCount([
                'borrows',
                'borrows as active_borrows_count' => function ($query) {
                    $query->where('status', 'borrowed');
                }
            ])
            ->get();

        return response()->json($userStats);
    }

    /**
     * Get overdue books (books borrowed more than 14 days ago)
     */
    public function overdueBooks(): JsonResponse
    {
        $overdueBooks = Borrow::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->where('borrowed_at', '<', now()->subDays(14))
            ->get();

        return response()->json($overdueBooks);
    }

    /**
     * Get monthly borrowing trends
     */
    public function monthlyTrends(): JsonResponse
    {
        $trends = Borrow::select(
                DB::raw('YEAR(borrowed_at) as year'),
                DB::raw('MONTH(borrowed_at) as month'),
                DB::raw('COUNT(*) as total_borrows')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json($trends);
    }
}