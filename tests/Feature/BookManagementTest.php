<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_author_can_create_a_book(): void
    {
        $author = User::factory()->author()->create();

        $response = $this->actingAs($author, 'api')->postJson('/api/books', [
            'title' => 'My First Book',
            'description' => 'A short story.',
            'genre' => 'Fiction',
        ]);

        $response->assertCreated()->assertJsonPath('data.title', 'My First Book');

        $this->assertDatabaseHas('books', ['title' => 'My First Book', 'author_id' => $author->id]);
    }

    public function test_creating_a_book_creates_an_initial_version(): void
    {
        $author = User::factory()->author()->create();

        $this->actingAs($author, 'api')->postJson('/api/books', ['title' => 'Versioned'])->assertCreated();

        $this->assertDatabaseCount('book_versions', 1);
        $this->assertDatabaseHas('book_versions', ['version_number' => 1]);
    }

    public function test_an_author_cannot_update_another_authors_book(): void
    {
        $owner = User::factory()->author()->create();
        $other = User::factory()->author()->create();
        $book = Book::factory()->for($owner, 'author')->create();

        $this->actingAs($other, 'api')
            ->putJson("/api/books/{$book->id}", ['title' => 'Hijacked'])
            ->assertForbidden();
    }

    public function test_a_published_book_is_read_only(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->for($author, 'author')->status(BookStatus::Published)->create();

        $this->actingAs($author, 'api')
            ->putJson("/api/books/{$book->id}", ['title' => 'New Title'])
            ->assertForbidden();
    }

    public function test_updating_a_book_creates_a_new_version(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->for($author, 'author')->create();

        $this->actingAs($author, 'api')
            ->putJson("/api/books/{$book->id}", ['title' => 'Updated'])
            ->assertOk();

        $this->assertDatabaseHas('book_versions', ['book_id' => $book->id, 'version_number' => 1]);
    }
}
