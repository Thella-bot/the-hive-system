<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::with('programmes')->get()->keyBy('name');

        $coursesData = [
            'Culinary Arts' => [
                ['code' => 'CA101', 'name' => 'Introduction to Culinary Arts'],
                ['code' => 'CA102', 'name' => 'Basic Knife Skills'],
                ['code' => 'CA201', 'name' => 'Stocks, Sauces, and Soups'],
                ['code' => 'CA202', 'name' => 'Global Cuisines'],
            ],
            'Pastry and Bakery' => [
                ['code' => 'PB101', 'name' => 'Introduction to Baking'],
                ['code' => 'PB102', 'name' => 'Breads and Doughs'],
                ['code' => 'PB201', 'name' => 'Cakes and Decorations'],
                ['code' => 'PB202', 'name' => 'Chocolate and Confectionery'],
            ],
            'Hospitality Management' => [
                ['code' => 'HM101', 'name' => 'Introduction to Hospitality'],
                ['code' => 'HM201', 'name' => 'Front Office Operations'],
            ],
            'Food and Beverage Service' => [
                ['code' => 'FBS101', 'name' => 'Restaurant Service Fundamentals'],
                ['code' => 'FBS201', 'name' => 'Wine and Beverage Management'],
            ],
            'Tourism Management' => [
                ['code' => 'TM101', 'name' => 'Principles of Tourism'],
                ['code' => 'TM201', 'name' => 'Sustainable Tourism Development'],
            ],
        ];

        foreach ($coursesData as $departmentName => $courses) {
            if (isset($departments[$departmentName])) {
                $department = $departments[$departmentName];
                foreach ($courses as $courseData) {
                    $course = Course::updateOrCreate(
                        ['code' => $courseData['code']],
                        ['name' => $courseData['name'], 'department_id' => $department->id]
                    );

                    // Attach course to all programmes within that department
                    foreach ($department->programmes as $programme) {
                        $programme->courses()->syncWithoutDetaching($course->id);
                    }
                }
            }
        }
    }
}