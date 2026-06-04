<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/books')->assertUnauthorized();
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }

    public function test_an_author_only_sees_their_own_books_in_the_index(): void
    {
        $author = User::factory()->author()->create();
        Book::factory()->for($author, 'author')->count(2)->create();
        Book::factory()->count(3)->create();

        $this->actingAs($author, 'api')
            ->getJson('/api/books')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_an_author_cannot_view_another_authors_book(): void
    {
        $owner = User::factory()->author()->create();
        $other = User::factory()->author()->create();
        $book = Book::factory()->for($owner, 'author')->create();

        $this->actingAs($other, 'api')
            ->getJson("/api/books/{$book->id}")
            ->assertForbidden();
    }

    public function test_a_reviewer_can_view_any_book(): void
    {
        $reviewer = User::factory()->reviewer()->create();
        $book = Book::factory()->create();

        $this->actingAs($reviewer, 'api')
            ->getJson("/api/books/{$book->id}")
            ->assertOk();
    }

    public function test_an_author_cannot_delete_another_authors_book(): void
    {
        $owner = User::factory()->author()->create();
        $other = User::factory()->author()->create();
        $book = Book::factory()->for($owner, 'author')->create();

        $this->actingAs($other, 'api')
            ->deleteJson("/api/books/{$book->id}")
            ->assertForbidden();
    }
}
