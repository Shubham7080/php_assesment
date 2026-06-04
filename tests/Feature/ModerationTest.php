<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\User;
use App\Services\ModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderation_flags_profanity(): void
    {
        config(['moderation.profanity' => ['damn']]);
        $book = Book::factory()->create(['title' => 'What the damn', 'description' => null]);

        $report = app(ModerationService::class)->moderate($book->load('chapters.pages'));

        $this->assertFalse($report['passed']);
        $this->assertContains('damn', $report['profanity']);
    }

    public function test_moderation_flags_restricted_words(): void
    {
        config(['moderation.restricted' => ['classified']]);
        $book = Book::factory()->create(['title' => 'Top classified file', 'description' => null]);

        $report = app(ModerationService::class)->moderate($book->load('chapters.pages'));

        $this->assertFalse($report['passed']);
        $this->assertContains('classified', $report['restricted']);
    }

    public function test_clean_content_passes_moderation(): void
    {
        config(['moderation.profanity' => ['damn'], 'moderation.restricted' => ['classified']]);
        $book = Book::factory()->create(['title' => 'A friendly book', 'description' => 'Nice words only.']);

        $report = app(ModerationService::class)->moderate($book->load('chapters.pages'));

        $this->assertTrue($report['passed']);
    }

    public function test_submitting_a_book_with_profanity_returns_it_to_draft(): void
    {
        config(['moderation.profanity' => ['damn']]);
        $author = User::factory()->author()->create();
        $book = Book::factory()->for($author, 'author')->create([
            'title' => 'A damn mess',
            'description' => null,
        ]);

        $this->actingAs($author, 'api')
            ->postJson("/api/books/{$book->id}/submit")
            ->assertOk();

        $fresh = $book->fresh();
        $this->assertSame(BookStatus::Draft, $fresh->status);
        $this->assertFalse($fresh->moderation_report['passed']);
    }
}
