<?php

namespace App\Services;

use App\Enums\BookStatus;
use App\Events\BookApproved;
use App\Events\BookPublished;
use App\Events\BookRejected;
use App\Events\BookSubmitted;
use App\Exceptions\WorkflowException;
use App\Models\Book;

class BookWorkflowService
{
    public function submit(Book $book): Book
    {
        if (! in_array($book->status, [BookStatus::Draft, BookStatus::Rejected], true)) {
            throw WorkflowException::invalidTransition($book->status->value, 'submit');
        }

        $book->update(['status' => BookStatus::Submitted, 'moderation_report' => null]);

        BookSubmitted::dispatch($book);

        return $book->refresh();
    }

    public function approve(Book $book): Book
    {
        if ($book->status !== BookStatus::UnderReview) {
            throw WorkflowException::invalidTransition($book->status->value, 'approve');
        }

        $book->update(['status' => BookStatus::Approved]);

        BookApproved::dispatch($book);

        return $book->refresh();
    }

    public function reject(Book $book, ?string $reason = null): Book
    {
        if (! in_array($book->status, [BookStatus::Submitted, BookStatus::UnderReview], true)) {
            throw WorkflowException::invalidTransition($book->status->value, 'reject');
        }

        $book->update(['status' => BookStatus::Rejected]);

        BookRejected::dispatch($book, $reason);

        return $book->refresh();
    }

    public function publish(Book $book): Book
    {
        if ($book->status !== BookStatus::Approved) {
            throw WorkflowException::invalidTransition($book->status->value, 'publish');
        }

        $book->update(['status' => BookStatus::Published, 'published_at' => now()]);

        BookPublished::dispatch($book);

        return $book->refresh();
    }
}
