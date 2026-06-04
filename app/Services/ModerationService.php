<?php

namespace App\Services;

use App\Models\Book;

class ModerationService
{
    public function moderate(Book $book): array
    {
        $text = $this->extractText($book);

        $profanity = $this->findMatches($text, (array) config('moderation.profanity'));
        $restricted = $this->findMatches($text, (array) config('moderation.restricted'));

        $passed = $profanity === [] && $restricted === [];

        return [
            'passed' => $passed,
            'profanity' => $profanity,
            'restricted' => $restricted,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    private function extractText(Book $book): string
    {
        $parts = [$book->title, (string) $book->description];

        foreach ($book->chapters as $chapter) {
            $parts[] = $chapter->title;

            foreach ($chapter->pages as $page) {
                $parts[] = strip_tags((string) $page->content);
            }
        }

        return mb_strtolower(implode(' ', $parts));
    }

    private function findMatches(string $text, array $words): array
    {
        $matches = [];

        foreach ($words as $word) {
            $word = mb_strtolower(trim($word));

            if ($word === '') {
                continue;
            }

            if (preg_match('/\b'.preg_quote($word, '/').'\b/u', $text)) {
                $matches[] = $word;
            }
        }

        return array_values(array_unique($matches));
    }
}
