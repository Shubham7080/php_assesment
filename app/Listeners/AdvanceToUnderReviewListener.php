<?php

namespace App\Listeners;

use App\Enums\BookStatus;
use App\Events\ModerationPassed;

class AdvanceToUnderReviewListener
{
    public function handle(ModerationPassed $event): void
    {
        $event->book->update(['status' => BookStatus::UnderReview]);
    }
}
