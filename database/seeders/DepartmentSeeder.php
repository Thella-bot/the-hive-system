<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            ['name' => 'Hospitality Management', 'slug' => 'hospitality-management', 'is_academic' => true],
            ['name' => 'Patisseries', 'slug' => 'patisseries', 'is_academic' => true],
            ['name' => 'Food Safety', 'slug' => 'food-safety', 'is_academic' => true],
            ['name' => 'Global Cuisines', 'slug' => 'global-cuisines', 'is_academic' => true],
            ['name' => 'Contemporary Gastronomy', 'slug' => 'contemporary-gastronomy', 'is_academic' => true],
            ['name' => 'Administration', 'slug' => 'administration', 'is_academic' => false],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate($department);
        }
    }
}
