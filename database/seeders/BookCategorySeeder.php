<?php

namespace Database\Seeders;

use App\Models\BookCategory;
use Illuminate\Database\Seeder;

class BookCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Culinary Arts', 'code' => 'CUL', 'description' => 'Cooking, baking, and food preparation'],
            ['name' => 'Hospitality Management', 'code' => 'HOS', 'description' => 'Hotel and restaurant management'],
            ['name' => 'Food Safety & Hygiene', 'code' => 'FSH', 'description' => 'Food safety and sanitation'],
            ['name' => 'Nutrition', 'code' => 'NUT', 'description' => 'Diet and nutrition'],
            ['name' => 'Business & Management', 'code' => 'BUS', 'description' => 'Business and management'],
            ['name' => 'Customer Service', 'code' => 'CUS', 'description' => 'Customer service and relations'],
            ['name' => 'Events Management', 'code' => 'EVT', 'description' => 'Event planning and coordination'],
            ['name' => 'Reference', 'code' => 'REF', 'description' => 'Reference materials'],
            ['name' => 'General Interest', 'code' => 'GEN', 'description' => 'General reading'],
        ];

        foreach ($categories as $category) {
            BookCategory::create($category);
        }
    }
}