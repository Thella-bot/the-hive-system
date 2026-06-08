<?php

namespace Database\Seeders;

use App\Models\ShortCourse;
use App\Models\Department;
use App\Models\Programme;
use Illuminate\Database\Seeder;

class ShortCourseSeeder extends Seeder
{
    public function run()
    {
        $contemporaryDept = Department::where('slug', 'contemporary-gastronomy')->first();
        $globalCuisinesDept = Department::where('slug', 'global-cuisines')->first();
        $patisseriesDept = Department::where('slug', 'patisseries')->first();
        $hospitalityDept = Department::where('slug', 'hospitality-management')->first();

        $shortCourses = [
            // Department-level short courses
            [
                'title' => 'Knife Skills Masterclass',
                'description' => 'Learn professional knife techniques including julienne, brunoise, chiffonade, and batonnet cuts. Essential for every aspiring chef.',
                'type' => 'workshop',
                'duration' => '1 Day',
                'price' => 450.00,
                'start_date' => '2026-07-15',
                'end_date' => '2026-07-15',
                'is_active' => true,
                'accepting_applications' => true,
            ],
            [
                'title' => 'Sourdough Bread Workshop',
                'description' => 'Master the art of sourdough from starter creation to shaping and baking. Take home your own loaf at the end of the session.',
                'type' => 'workshop',
                'duration' => '2 Days',
                'price' => 850.00,
                'start_date' => '2026-07-22',
                'end_date' => '2026-07-23',
                'is_active' => true,
                'accepting_applications' => true,
            ],
            [
                'title' => 'Plating & Presentation',
                'description' => 'Elevate your dishes with professional plating techniques. Learn to create stunning visuals using negative space, color theory, and garnishes.',
                'type' => 'training',
                'duration' => '3 Days',
                'price' => 1200.00,
                'start_date' => '2026-08-05',
                'end_date' => '2026-08-07',
                'is_active' => true,
                'accepting_applications' => true,
            ],
            [
                'title' => 'Pastry Fundamentals',
                'description' => 'A hands-on introduction to classical pastry techniques. Learn the foundations of puff pastry, choux, and shortcrust through practical sessions.',
                'type' => 'short-course',
                'duration' => '1 Week',
                'price' => 2500.00,
                'start_date' => '2026-08-12',
                'end_date' => '2026-08-16',
                'is_active' => true,
                'accepting_applications' => true,
            ],
            [
                'title' => 'Food Safety & Hygiene Certification',
                'description' => 'Earn your internationally recognized food safety certificate. Covers HACCP principles, personal hygiene, and safe food handling practices.',
                'type' => 'training',
                'duration' => '3 Days',
                'price' => 1500.00,
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-03',
                'is_active' => true,
                'accepting_applications' => true,
            ],
            [
                'title' => 'Chocolate & Tempering Techniques',
                'description' => 'Explore the world of chocolate from bean to bonbon. Learn proper tempering methods and create your own hand-dipped chocolates.',
                'type' => 'workshop',
                'duration' => '2 Days',
                'price' => 1100.00,
                'start_date' => '2026-09-08',
                'end_date' => '2026-09-09',
                'is_active' => true,
                'accepting_applications' => true,
            ],
            [
                'title' => 'Garde Manger Essentials',
                'description' => 'Discover the art of cold food preparation including pates, terrines, salads, and buffet presentations used in professional kitchens.',
                'type' => 'short-course',
                'duration' => '1 Week',
                'price' => 2800.00,
                'start_date' => '2026-10-05',
                'end_date' => '2026-10-09',
                'is_active' => true,
                'accepting_applications' => true,
            ],
            [
                'title' => 'Beverage Service & Barista Skills',
                'description' => 'Learn to prepare espresso-based drinks, mocktails, and specialty beverages. Covers latte art, coffee sourcing, and customer service.',
                'type' => 'training',
                'duration' => '3 Days',
                'price' => 950.00,
                'start_date' => '2026-10-20',
                'end_date' => '2026-10-22',
                'is_active' => true,
                'accepting_applications' => true,
            ],
        ];

        // Assign courses to departments
        $assignments = [
            0 => $contemporaryDept, // Knife Skills
            1 => $patisseriesDept,  // Sourdough
            2 => $globalCuisinesDept, // Plating
            3 => $contemporaryDept, // Pastry Fundamentals
            4 => $hospitalityDept,  // Food Safety
            5 => $patisseriesDept,  // Chocolate
            6 => $globalCuisinesDept, // Garde Manger
            7 => $hospitalityDept,  // Beverage Service
        ];

        foreach ($shortCourses as $index => $data) {
            $dept = $assignments[$index];
            ShortCourse::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'courseable_type' => Department::class,
                    'courseable_id' => $dept->id,
                ])
            );
        }

        // Programme-linked short courses (under "Short Courses and Cooking Sessions" programme)
        $programme = Programme::where('name', 'Short Courses and Cooking Sessions')->first();

        if ($programme) {
            $programmeCourses = [
                [
                    'title' => 'Weekend Cooking for Beginners',
                    'description' => 'Perfect for home wanting to build confidence in the kitchen. Learn basic techniques, simple recipes, and kitchen safety.',
                    'type' => 'short-course',
                    'duration' => '3 DaysSat-Sun-Sat)',
                    'price' => 650.00,
                    'start_date' => '2026-07-18',
                    'end_date' => '2026-07-20',
                    'is_active' => true,
                    'accepting_applications' => true,
                ],
                [
                    'title' => 'Kids Baking Club',
                    'description' => 'A fun, interactive baking class for children aged 8-14. Kids will make cookies, cupcakes, and simple breads in a safe environment.',
                    'type' => 'workshop',
                    'duration' => '1 Day',
                    'price' => 350.00,
                    'start_date' => '2026-08-01',
                    'end_date' => '2026-08-01',
                    'is_active' => true,
                    'accepting_applications' => true,
                ],
                [
                    'title' => 'Professional Pasta Making',
                    'description' => 'From dough to finished dish — learn to make fresh pasta by hand including tagliatelle, ravioli, and gnocchi.',
                    'type' => 'workshop',
                    'duration' => '2 Days',
                    'price' => 750.00,
                    'start_date' => '2026-08-26',
                    'end_date' => '2026-08-27',
                    'is_active' => true,
                    'accepting_applications' => true,
                ],
            ];

            foreach ($programmeCourses as $data) {
                ShortCourse::updateOrCreate(
                    ['title' => $data['title']],
                    array_merge($data, [
                        'courseable_type' => Programme::class,
                        'courseable_id' => $programme->id,
                    ])
                );
            }
        }
    }
}
