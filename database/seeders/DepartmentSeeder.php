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
            ['name' => 'Hospitality Management', 'slug' => 'hospitality-management',],
            ['name' => 'Patisseries', 'slug' => 'patisseries',],
            ['name' => 'Food Safety', 'slug' => 'food-safety',],
            ['name' => 'Global Cuisines', 'slug' => 'global-cuisines',],
            ['name' => 'Contemporary Gastronomy', 'slug' => 'contemporary-gastronomy',],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
