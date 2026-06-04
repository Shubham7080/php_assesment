<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\BookWorkflowService;
use Illuminate\Http\JsonResponse;

class BookWorkflowController extends Controller
{
    public function __construct(private BookWorkflowService $workflow) {}

    public function submit(Book $book): JsonResponse
    {
        $this->authorize('submit', $book);

        return $this->respond($this->workflow->submit($book));
    }

    public function approve(Book $book): JsonResponse
    {
        $this->authorize('review', $book);

        return $this->respond($this->workflow->approve($book));
    }

    public function reject(RejectBookRequest $request, Book $book): JsonResponse
    {
        $this->authorize('review', $book);

        return $this->respond($this->workflow->reject($book, $request->input('reason')));
    }

    public function publish(Book $book): JsonResponse
    {
        $this->authorize('publish', $book);

        return $this->respond($this->workflow->publish($book));
    }

    private function respond(Book $book): JsonResponse
    {
        return response()->json(['data' => new BookResource($book)]);
    }
}
