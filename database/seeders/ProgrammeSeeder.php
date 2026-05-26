<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programme;
use App\Models\Department;
use Illuminate\Support\Facades\Schema;

class ProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hospitalityDept    = Department::where('slug', 'hospitality-management')->first();
        $patisserieDept     = Department::where('slug', 'patisseries')->first();
        $contemporaryDept   = Department::where('slug', 'contemporary-gastronomy')->first();
        $globalCuisinesDept = Department::where('slug', 'global-cuisines')->first();

        // Safety check: Ensure departments exist before seeding
        if (!$hospitalityDept || !$patisserieDept || !$contemporaryDept || !$globalCuisinesDept) {
            $this->command->error('One or more required departments not found! Please check DepartmentSeeder slugs.');
            return;
        }

        $programmes = [
            [
                'name' => 'Higher Certificate in Contemporary Gastronomy',
                'description' => 'LGCSE, Junior Certificate(JC)',
                'duration' => '1 Year',
                'total_price' => 34000.00,
                'monthly_fee' => 2570.00,
                'registration_fee' => 2800.00,
                'academic_resource_fee' => 1500.00,
                'department_id' => $contemporaryDept->id, 
            ],
            [
                'name' => 'Diploma in Professional Cheffing',
                'description' => 'LGCSE',
                'duration' => '3 Years',
                'total_price' => 99000.00,
                'monthly_fee' => 2570.00,
                'registration_fee' => 3100.00,
                'academic_resource_fee' => 5000.00,
                'department_id' => $globalCuisinesDept->id, 
            ],
            [
                'name' => 'Patisserie and Baking',
                'description' => 'LGCSE, Junior Certificate(JC)',
                'duration' => '1 Year & 2 Years, 3 months - 6 Months, 1 - 5 Days',
                'total_price' => 0.00,             // Added defaults
                'monthly_fee' => 0.00,             
                'registration_fee' => 0.00,        
                'academic_resource_fee' => 0.00,   
                'department_id' => $patisserieDept->id, // Mapped to Patisseries
            ],
            [
                'name' => 'Gastronomy Cooking and Patisserie',
                'description' => 'LGCSE, Junior Certificate(JC)',
                'duration' => '6 Months',
                'total_price' => 18500.00,
                'monthly_fee' => 2570.00,
                'registration_fee' => 2000.00,
                'academic_resource_fee' => 1500.00,
                'department_id' => $patisserieDept->id, // Mapped to Patisseries
            ],
            [
                'name' => 'Diploma in Culinary Patisserie',
                'description' => 'LGCSE, Junior Certificate(JC)',
                'duration' => '2 Years',
                'total_price' => 66300.00,
                'monthly_fee' => 2570.00,
                'registration_fee' => 2500.00,
                'academic_resource_fee' => 2800.00,
                'department_id' => $patisserieDept->id, // Mapped to Patisseries
            ],
            [
                'name' => 'Hospitality Management',
                'description' => 'LGCSE, Junior Certificate(JC)',
                'duration' => '1 Year',
                'total_price' => 32500.00,
                'monthly_fee' => 2500.00,
                'registration_fee' => 2800.00,
                'academic_resource_fee' => 2500.00,
                'uniform_fee' => 3800.00,           // Ensure this column is in your migration!
                'department_id' => $hospitalityDept->id, // Mapped to Hospitality Management
            ],
            [
                'name' => 'Short Courses and Cooking Sessions',
                'description' => 'LGCSE, Junior Certificate(JC)',
                'duration' => '3 months - 6 Months, 1 - 5 Days',
                'total_price' => 0.00,             
                'monthly_fee' => 0.00,             
                'registration_fee' => 0.00,        
                'academic_resource_fee' => 0.00,   
                'department_id' => $globalCuisinesDept->id,
            ],
        ];

        // 3. Insert data
        foreach ($programmes as $programme) {
            if (!Schema::hasColumn('programmes', 'uniform_fee') && isset($programme['uniform_fee'])) {
                unset($programme['uniform_fee']);
            }

            Programme::create($programme);
        }
    }
}
