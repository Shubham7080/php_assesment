<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_a_clean_book_moves_it_to_under_review(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->for($author, 'author')->create([
            'title' => 'A Pleasant Tale',
            'description' => 'A gentle story about kindness.',
        ]);

        $this->actingAs($author, 'api')
            ->postJson("/api/books/{$book->id}/submit")
            ->assertOk();

        $this->assertSame(BookStatus::UnderReview, $book->fresh()->status);
    }

    public function test_only_a_reviewer_can_approve(): void
    {
        $author = User::factory()->author()->create();
        $reviewer = User::factory()->reviewer()->create();
        $book = Book::factory()->for($author, 'author')->status(BookStatus::UnderReview)->create();

        $this->actingAs($author, 'api')
            ->postJson("/api/books/{$book->id}/approve")
            ->assertForbidden();

        $this->actingAs($reviewer, 'api')
            ->postJson("/api/books/{$book->id}/approve")
            ->assertOk();

        $this->assertSame(BookStatus::Approved, $book->fresh()->status);
    }

    public function test_only_an_admin_can_publish_an_approved_book(): void
    {
        $author = User::factory()->author()->create();
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->for($author, 'author')->status(BookStatus::Approved)->create();

        $this->actingAs($author, 'api')
            ->postJson("/api/books/{$book->id}/publish")
            ->assertForbidden();

        $this->actingAs($admin, 'api')
            ->postJson("/api/books/{$book->id}/publish")
            ->assertOk();

        $this->assertSame(BookStatus::Published, $book->fresh()->status);
    }

    public function test_a_book_cannot_be_published_before_approval(): void
    {
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->status(BookStatus::Draft)->create();

        $this->actingAs($admin, 'api')
            ->postJson("/api/books/{$book->id}/publish")
            ->assertStatus(422);
    }

    public function test_a_reviewer_can_reject_a_book(): void
    {
        $reviewer = User::factory()->reviewer()->create();
        $book = Book::factory()->status(BookStatus::UnderReview)->create();

        $this->actingAs($reviewer, 'api')
            ->postJson("/api/books/{$book->id}/reject", ['reason' => 'Needs work.'])
            ->assertOk();

        $this->assertSame(BookStatus::Rejected, $book->fresh()->status);
    }
}
