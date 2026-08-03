<?php

namespace Database\Factories;

use App\Models\BookCategory;
use App\Models\LibraryBook;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryBookFactory extends Factory
{
    protected $model = LibraryBook::class;

    public function definition(): array
    {
        return [
            'isbn' => $this->faker->unique()->isbn13(),
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'publisher' => $this->faker->company(),
            'publish_year' => $this->faker->year(),
            'edition' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'category_id' => BookCategory::factory(),
            'total_copies' => 5,
            'available_copies' => 5,
            'location' => $this->faker->word(),
            'call_number' => $this->faker->word(),
            'is_available' => true,
            'is_active' => true,
        ];
    }
}
