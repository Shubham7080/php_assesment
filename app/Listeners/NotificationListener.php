<?php

namespace App\Listeners;

use App\Events\BookApproved;
use App\Events\BookPublished;
use App\Events\BookRejected;
use App\Events\ModerationFailed;
use Illuminate\Support\Facades\Log;

class NotificationListener
{
    public function handleApproved(BookApproved $event): void
    {
        $this->notify($event->book->author_id, "Your book '{$event->book->title}' was approved.");
    }

    public function handleRejected(BookRejected $event): void
    {
        $reason = $event->reason ? " Reason: {$event->reason}" : '';
        $this->notify($event->book->author_id, "Your book '{$event->book->title}' was rejected.".$reason);
    }

    public function handlePublished(BookPublished $event): void
    {
        $this->notify($event->book->author_id, "Your book '{$event->book->title}' is now published.");
    }

    public function handleModerationFailed(ModerationFailed $event): void
    {
        $this->notify($event->book->author_id, "Your book '{$event->book->title}' failed moderation and was returned to draft.");
    }

    private function notify(int $userId, string $message): void
    {
        Log::info('notification', ['user_id' => $userId, 'message' => $message]);
    }
}
