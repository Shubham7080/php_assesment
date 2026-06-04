<?php

namespace App\Listeners;

use App\Events\BookCreated;
use App\Events\BookPublished;
use App\Events\BookVersionCreated;
use Illuminate\Support\Facades\Log;

class AnalyticsListener
{
    public function handleCreated(BookCreated $event): void
    {
        Log::info('analytics.book_created', ['book_id' => $event->book->id, 'author_id' => $event->book->author_id]);
    }

    public function handleVersionCreated(BookVersionCreated $event): void
    {
        Log::info('analytics.version_created', ['book_id' => $event->version->book_id, 'version' => $event->version->version_number]);
    }

    public function handlePublished(BookPublished $event): void
    {
        Log::info('analytics.book_published', ['book_id' => $event->book->id]);
    }
}
