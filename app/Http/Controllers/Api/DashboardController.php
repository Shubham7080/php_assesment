<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $books = Book::query()->where('author_id', $user->id);

        $countsByStatus = (clone $books)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total_books' => (clone $books)->count(),
            'drafts' => (int) ($countsByStatus[BookStatus::Draft->value] ?? 0),
            'submitted' => (int) ($countsByStatus[BookStatus::Submitted->value] ?? 0),
            'under_review' => (int) ($countsByStatus[BookStatus::UnderReview->value] ?? 0),
            'approved' => (int) ($countsByStatus[BookStatus::Approved->value] ?? 0),
            'rejected' => (int) ($countsByStatus[BookStatus::Rejected->value] ?? 0),
            'published' => (int) ($countsByStatus[BookStatus::Published->value] ?? 0),
        ];

        return response()->json([
            'data' => [
                'stats' => $stats,
                'recent_books' => BookResource::collection((clone $books)->latest()->limit(5)->get()),
            ],
        ]);
    }
}
