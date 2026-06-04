<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Services\BookVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_version_snapshot_captures_chapters_and_pages(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->for($author, 'author')->create();
        $chapter = $book->chapters()->create(['title' => 'Chapter 1', 'position' => 1]);
        $chapter->pages()->create(['position' => 1, 'content' => '<p>Hello</p>']);

        $version = app(BookVersionService::class)->snapshot($book->fresh(), $author->id);

        $this->assertSame(1, $version->version_number);
        $this->assertSame('Chapter 1', $version->snapshot['chapters'][0]['title']);
        $this->assertSame('<p>Hello</p>', $version->snapshot['chapters'][0]['pages'][0]['content']);
    }

    public function test_version_numbers_increment_per_book(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->for($author, 'author')->create();
        $service = app(BookVersionService::class);

        $service->snapshot($book, $author->id);
        $second = $service->snapshot($book, $author->id);

        $this->assertSame(2, $second->version_number);
    }

    public function test_an_author_can_list_book_versions(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->for($author, 'author')->create();
        app(BookVersionService::class)->snapshot($book, $author->id);

        $this->actingAs($author, 'api')
            ->getJson("/api/books/{$book->id}/versions")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
