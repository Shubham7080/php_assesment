<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Chapter;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PageController extends Controller
{
    public function index(Chapter $chapter): AnonymousResourceCollection
    {
        $this->authorize('view', $chapter->book);

        return PageResource::collection($chapter->pages);
    }

    public function store(StorePageRequest $request, Chapter $chapter): JsonResponse
    {
        $this->authorize('update', $chapter->book);

        $page = $chapter->pages()->create([
            'content' => $request->string('content'),
            'position' => $request->integer('position') ?: (((int) $chapter->pages()->max('position')) + 1),
        ]);

        return response()->json(['data' => new PageResource($page)], 201);
    }

    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page->chapter->book);

        $page->update($request->validated());

        return response()->json(['data' => new PageResource($page)]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('update', $page->chapter->book);

        $page->delete();

        return response()->json(['message' => 'Page deleted.']);
    }
}
