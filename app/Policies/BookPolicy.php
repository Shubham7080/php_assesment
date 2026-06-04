<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function view(User $user, Book $book): bool
    {
        return $book->author_id === $user->id || $user->isReviewer() || $user->isAdmin();
    }

    public function update(User $user, Book $book): bool
    {
        return $book->author_id === $user->id && ! $book->isPublished();
    }

    public function delete(User $user, Book $book): bool
    {
        return $book->author_id === $user->id && ! $book->isPublished();
    }

    public function submit(User $user, Book $book): bool
    {
        return $book->author_id === $user->id && $user->isAuthor();
    }

    public function review(User $user, Book $book): bool
    {
        return $user->isReviewer();
    }

    public function publish(User $user, Book $book): bool
    {
        return $user->isAdmin();
    }
}
