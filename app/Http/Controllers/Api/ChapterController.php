<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Http\Resources\ChapterResource;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChapterController extends Controller
{
    public function index(Book $book): AnonymousResourceCollection
    {
        $this->authorize('view', $book);

        return ChapterResource::collection($book->chapters()->with('pages')->get());
    }

    public function store(StoreChapterRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $chapter = $book->chapters()->create([
            'title' => $request->string('title'),
            'position' => $request->integer('position') ?: (((int) $book->chapters()->max('position')) + 1),
        ]);

        return response()->json(['data' => new ChapterResource($chapter)], 201);
    }

    public function update(UpdateChapterRequest $request, Chapter $chapter): JsonResponse
    {
        $this->authorize('update', $chapter->book);

        $chapter->update($request->validated());

        return response()->json(['data' => new ChapterResource($chapter)]);
    }

    public function destroy(Chapter $chapter): JsonResponse
    {
        $this->authorize('update', $chapter->book);

        $chapter->delete();

        return response()->json(['message' => 'Chapter deleted.']);
    }
}
