<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'title' => fake()->sentence(3),
            'position' => fake()->numberBetween(1, 20),
        ];
    }
}
