<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookVersionResource;
use App\Models\Book;
use App\Models\BookVersion;
use App\Services\BookVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BookVersionController extends Controller
{
    public function __construct(private BookVersionService $versions) {}

    public function index(Book $book): AnonymousResourceCollection
    {
        $this->authorize('view', $book);

        return BookVersionResource::collection($book->versions);
    }

    public function store(Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $version = $this->versions->snapshot($book->load('chapters.pages'), Auth::id());

        return response()->json(['data' => new BookVersionResource($version)], 201);
    }

    public function show(Book $book, BookVersion $version): JsonResponse
    {
        $this->authorize('view', $book);

        abort_unless($version->book_id === $book->id, 404);

        return response()->json(['data' => new BookVersionResource($version)]);
    }
}
