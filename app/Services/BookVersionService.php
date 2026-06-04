<?php

namespace App\Services;

use App\Events\BookVersionCreated;
use App\Models\Book;
use App\Models\BookVersion;
use Illuminate\Support\Facades\DB;

class BookVersionService
{
    public function snapshot(Book $book, ?int $userId = null): BookVersion
    {
        return DB::transaction(function () use ($book, $userId) {
            $book->loadMissing('chapters.pages');

            $nextNumber = ((int) $book->versions()->max('version_number')) + 1;

            $version = $book->versions()->create([
                'created_by' => $userId,
                'version_number' => $nextNumber,
                'snapshot' => $this->buildSnapshot($book),
            ]);

            BookVersionCreated::dispatch($version);

            return $version;
        });
    }

    public function rollback(Book $book, BookVersion $version, ?int $userId = null): Book
    {
        return DB::transaction(function () use ($book, $version, $userId) {
            $snapshot = $version->snapshot;

            $book->update([
                'title' => $snapshot['title'] ?? $book->title,
                'description' => $snapshot['description'] ?? $book->description,
                'genre' => $snapshot['genre'] ?? $book->genre,
            ]);

            $book->chapters()->delete();

            foreach ($snapshot['chapters'] ?? [] as $chapterData) {
                $chapter = $book->chapters()->create([
                    'title' => $chapterData['title'],
                    'position' => $chapterData['position'],
                ]);

                foreach ($chapterData['pages'] ?? [] as $pageData) {
                    $chapter->pages()->create([
                        'position' => $pageData['position'],
                        'content' => $pageData['content'],
                    ]);
                }
            }

            $this->snapshot($book->fresh(), $userId);

            return $book->fresh('chapters.pages');
        });
    }

    private function buildSnapshot(Book $book): array
    {
        return [
            'title' => $book->title,
            'description' => $book->description,
            'genre' => $book->genre,
            'status' => $book->status->value,
            'chapters' => $book->chapters->map(fn ($chapter) => [
                'title' => $chapter->title,
                'position' => $chapter->position,
                'pages' => $chapter->pages->map(fn ($page) => [
                    'position' => $page->position,
                    'content' => $page->content,
                ])->all(),
            ])->all(),
        ];
    }
}
