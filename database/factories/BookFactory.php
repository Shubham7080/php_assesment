<?php

namespace Database\Factories;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_id' => User::factory()->author(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'genre' => fake()->randomElement(['Fiction', 'Non-Fiction', 'Sci-Fi', 'Fantasy']),
            'status' => BookStatus::Draft,
        ];
    }

    public function status(BookStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
