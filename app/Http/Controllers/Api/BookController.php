<?php

namespace App\Http\Controllers\Api;

use App\Events\BookCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\BookVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function __construct(private BookVersionService $versions) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Book::query()->with('author')->latest();

        if ($user->isAuthor()) {
            $query->where('author_id', $user->id);
        }

        return BookResource::collection($query->paginate(15));
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = Book::create([
            ...$request->validated(),
            'author_id' => Auth::id(),
        ]);

        BookCreated::dispatch($book);
        $this->versions->snapshot($book, Auth::id());

        return response()->json(['data' => new BookResource($book->load('author'))], 201);
    }

    public function show(Book $book): JsonResponse
    {
        $this->authorize('view', $book);

        return response()->json(['data' => new BookResource($book->load('author', 'chapters.pages'))]);
    }

    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $book->update($request->validated());

        $this->versions->snapshot($book, Auth::id());

        return response()->json(['data' => new BookResource($book->load('author'))]);
    }

    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(['message' => 'Book deleted.']);
    }
}
