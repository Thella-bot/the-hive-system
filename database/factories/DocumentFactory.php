<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'category' => 'general',
            'visible_to_roles' => null,
            'is_published' => true,
            'created_by' => null,
        ];
    }
}

