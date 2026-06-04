<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'position' => fake()->numberBetween(1, 30),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
        ];
    }
}
