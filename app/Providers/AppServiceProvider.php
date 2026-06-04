<?php

namespace App\Providers;

use App\Events\BookApproved;
use App\Events\BookCreated;
use App\Events\BookPublished;
use App\Events\BookRejected;
use App\Events\BookSubmitted;
use App\Events\BookVersionCreated;
use App\Events\ModerationFailed;
use App\Events\ModerationPassed;
use App\Listeners\AdvanceToUnderReviewListener;
use App\Listeners\AnalyticsListener;
use App\Listeners\ModerationListener;
use App\Listeners\NotificationListener;
use App\Models\Book;
use App\Policies\BookPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Book::class, BookPolicy::class);

        Event::listen(BookSubmitted::class, ModerationListener::class);
        Event::listen(ModerationPassed::class, AdvanceToUnderReviewListener::class);

        Event::listen(BookCreated::class, [AnalyticsListener::class, 'handleCreated']);
        Event::listen(BookVersionCreated::class, [AnalyticsListener::class, 'handleVersionCreated']);
        Event::listen(BookPublished::class, [AnalyticsListener::class, 'handlePublished']);

        Event::listen(BookApproved::class, [NotificationListener::class, 'handleApproved']);
        Event::listen(BookRejected::class, [NotificationListener::class, 'handleRejected']);
        Event::listen(BookPublished::class, [NotificationListener::class, 'handlePublished']);
        Event::listen(ModerationFailed::class, [NotificationListener::class, 'handleModerationFailed']);
    }
}
