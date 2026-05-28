<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programme;
use App\Models\Department;
use App\Models\ProgrammeVariant;
use App\Models\Module;
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

            $created = Programme::updateOrCreate($programme);

            // 4. Create default variants for each programme
            $variants = [
                [
                    'label' => 'Full Programme',
                    'duration' => $created->duration,
                    'total_price' => $created->total_price,
                    'monthly_fee' => $created->monthly_fee,
                    'is_active' => true,
                ],
            ];

            // Add installment variant for paid programmes
            if ($created->total_price > 0) {
                $variants[] = [
                    'label' => 'Monthly Installments',
                    'duration' => $created->duration,
                    'total_price' => $created->total_price,
                    'monthly_fee' => max(500, $created->monthly_fee),
                    'is_active' => true,
                ];
            }

            foreach ($variants as $variant) {
                ProgrammeVariant::firstOrCreate([
                    'programme_id' => $created->id,
                    'label' => $variant['label'],
                ], $variant);
            }

            // Seed default modules for each programme
            $this->seedProgrammeModules($created);
        }
    }

    private function seedProgrammeModules(Programme $programme): void
    {
        if ($programme->modules()->exists()) {
            return; // Skip if modules already exist
        }

        $department = $programme->department;
        if (! $department) {
            return;
        }

        $moduleTemplates = [
            'hospitality-management' => [
                ['code' => 'HM101', 'name' => 'Introduction to Hospitality', 'description' => 'Foundations of hospitality management'],
                ['code' => 'HM102', 'name' => 'Front Office Operations', 'description' => 'Hotel front office management'],
                ['code' => 'HM103', 'name' => 'Food & Beverage Management', 'description' => 'Managing food and beverage operations'],
                ['code' => 'HM104', 'name' => 'Housekeeping Operations', 'description' => 'Hotel housekeeping management'],
            ],
            'patisseries' => [
                ['code' => 'PB101', 'name' => 'Introduction to Baking', 'description' => 'Fundamentals of baking and pastry'],
                ['code' => 'PB102', 'name' => 'Breads and Doughs', 'description' => 'Artisan bread making'],
                ['code' => 'PB103', 'name' => 'Cakes and Decorations', 'description' => 'Cake decorating techniques'],
                ['code' => 'PB104', 'name' => 'Chocolate and Confectionery', 'description' => 'Chocolate work and confectionery'],
            ],
            'contemporary-gastronomy' => [
                ['code' => 'CG101', 'name' => 'Introduction to Culinary Arts', 'description' => 'Fundamentals of cooking'],
                ['code' => 'CG102', 'name' => 'Food Safety & Hygiene', 'description' => 'HACCP and food safety'],
                ['code' => 'CG103', 'name' => 'Baking & Patisserie Basics', 'description' => 'Introduction to baking'],
                ['code' => 'CG104', 'name' => 'Global Cuisines', 'description' => 'Culinary traditions worldwide'],
            ],
            'global-cuisines' => [
                ['code' => 'GC101', 'name' => 'Culinary Foundations', 'description' => 'Basic culinary techniques'],
                ['code' => 'GC102', 'name' => 'Knife Skills', 'description' => 'Professional knife skills'],
                ['code' => 'GC103', 'name' => 'Stocks, Sauces and Soups', 'description' => 'Classical stocks and sauces'],
                ['code' => 'GC104', 'name' => 'Global Cuisines', 'description' => 'International culinary traditions'],
                ['code' => 'GC105', 'name' => 'Kitchen Operations', 'description' => 'Commercial kitchen management'],
            ],
        ];

        $slug = $department->slug;
        $modules = $moduleTemplates[$slug] ?? [];

        foreach ($modules as $index => $moduleData) {
            $code = $moduleData['code'];
            Module::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $moduleData['name'],
                    'description' => $moduleData['description'],
                    'programme_id' => $programme->id,
                    'department_id' => $department->id,
                ]
            );
        }
    }
}
