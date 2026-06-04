<?php

namespace App\Listeners;

use App\Enums\BookStatus;
use App\Events\BookSubmitted;
use App\Events\ModerationFailed;
use App\Events\ModerationPassed;
use App\Services\ModerationService;

class ModerationListener
{
    public function __construct(private ModerationService $moderation) {}

    public function handle(BookSubmitted $event): void
    {
        $book = $event->book->loadMissing('chapters.pages');

        $report = $this->moderation->moderate($book);

        $book->update(['moderation_report' => $report]);

        if ($report['passed']) {
            ModerationPassed::dispatch($book);

            return;
        }

        $book->update(['status' => BookStatus::Draft]);

        ModerationFailed::dispatch($book, $report);
    }
}
