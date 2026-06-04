<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadDocumentRequest;
use App\Http\Resources\ChapterResource;
use App\Models\Book;
use App\Services\DocumentConversionService;
use Illuminate\Http\JsonResponse;

class DocumentUploadController extends Controller
{
    public function __construct(private DocumentConversionService $converter) {}

    public function store(UploadDocumentRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $chapter = $this->converter->convert($book, $request->file('document'));

        return response()->json([
            'message' => 'Document converted into pages.',
            'data' => new ChapterResource($chapter->load('pages')),
        ], 201);
    }
}
