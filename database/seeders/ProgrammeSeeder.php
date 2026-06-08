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
            // Higher Certificate in Contemporary Gastronomy
            [
                'name' => 'Higher Certificate in Contemporary Gastronomy',
                'description' => 'This programme is designed to provide students with foundational knowledge and skills in contemporary gastronomy. Students will learn about food safety, nutrition, culinary techniques, and kitchen operations.',
                'duration' => '1 Year',
                'duration_months' => 12,
                'requirements' => 'LGCSE or JC with at least D in English and Mathematics',
                'payment_method' => 'both',
                'total_price' => 34000.00,
                'monthly_fee' => 2570.00,
                'registration_fee' => 2500.00,
                'academic_resource_fee' => 1500.00,
                'uniform_fee' => 3400.00,
                'tools_cost' => 750.00,
                'intake_period' => 'January & July',
                'career_opportunities' => 'Junior Chef, Line Cook, Kitchen Assistant, Food Service Associate',
                'department_id' => $contemporaryDept->id,
            ],
            // Diploma in Professional Chef
            [
                'name' => 'Diploma in Professional Chef',
                'description' => 'This comprehensive diploma programme prepares students for a career as a professional chef. The programme covers advanced cooking techniques, kitchen management, menu planning, and industry placement.',
                'duration' => '2 Years & 6 months',
                'duration_months' => 30,
                'requirements' => 'LGCSE with at least D in English and Mathematics',
                'payment_method' => 'both',
                'total_price' => 99000.00,
                'monthly_fee' => 2570.00,
                'registration_fee' => 3100.00,
                'academic_resource_fee' => 5900.00,
                'uniform_fee' => 3400.00,
                'tools_cost' => 750.00,
                'intake_period' => 'January only',
                'career_opportunities' => 'Commis Chef, Chef de Partie, Sous Chef, Executive Chef, Kitchen Manager',
                'department_id' => $globalCuisinesDept->id,
            ],
            // Diploma in Culinary Patisserie
            [
                'name' => 'Diploma in Culinary Patisserie',
                'description' => 'This programme focuses on the art of patisserie and baking. Students will master classical French pastry techniques, chocolate work, sugar art, and bread making.',
                'duration' => '2 Years',
                'duration_months' => 24,
                'requirements' => 'LGCSE or JC with at least D in English and Mathematics',
                'payment_method' => 'both',
                'total_price' => 66300.00,
                'monthly_fee' => 2570.00,
                'registration_fee' => 2500.00,
                'academic_resource_fee' => 2800.00,
                'uniform_fee' => 3400.00,
                'tools_cost' => 750.00,
                'intake_period' => 'January & July',
                'career_opportunities' => 'Pastry Chef, Baker, Confectioner, Cake Designer, Patisserie Manager',
                'department_id' => $patisserieDept->id,
            ],
            // Hospitality Management
            [
                'name' => 'Hospitality Management',
                'description' => 'This programme prepares students for supervisory and management positions in the hospitality industry. Topics include hotel operations, food and beverage management, front office management, and event planning.',
                'duration' => '1 Year',
                'duration_months' => 12,
                'requirements' => 'LGCSE or JC with at least D in English and Mathematics',
                'payment_method' => 'both',
                'total_price' => 32500.00,
                'monthly_fee' => 2500.00,
                'registration_fee' => 2800.00,
                'academic_resource_fee' => 2500.00,
                'uniform_fee' => 3800.00,
                'tools_cost' => 0.00,
                'intake_period' => 'January & July',
                'career_opportunities' => 'Front Office Manager, Food & Beverage Supervisor, Event Coordinator, Hotel Manager',
                'department_id' => $hospitalityDept->id,
            ],
            // Short Courses and Cooking Sessions
            [
                'name' => 'Short Courses and Cooking Sessions',
                'description' => 'Intensive workshops and short courses for those looking to develop specific culinary skills without committing to a full programme.',
                'duration' => '3 months - 6 Months, 1 - 5 Days',
                'duration_months' => null,
                'requirements' => 'None required for most short courses',
                'payment_method' => 'monthly',
                'total_price' => 0.00,
                'monthly_fee' => 0.00,
                'registration_fee' => 0.00,
                'academic_resource_fee' => 0.00,
                'uniform_fee' => 0.00,
                'tools_cost' => 0.00,
                'intake_period' => 'Rolling intake',
                'career_opportunities' => 'Skill enhancement for personal or career development',
                'department_id' => $globalCuisinesDept->id,
            ],
        ];

        // 3. Insert data
        foreach ($programmes as $programme) {
            // Remove fields that don't exist yet if migration hasn't run
            $safeProgramme = $programme;
            if (!Schema::hasColumn('programmes', 'uniform_fee') && isset($safeProgramme['uniform_fee'])) {
                unset($safeProgramme['uniform_fee']);
            }
            if (!Schema::hasColumn('programmes', 'tools_cost') && isset($safeProgramme['tools_cost'])) {
                unset($safeProgramme['tools_cost']);
            }
            if (!Schema::hasColumn('programmes', 'duration_months') && isset($safeProgramme['duration_months'])) {
                unset($safeProgramme['duration_months']);
            }
            if (!Schema::hasColumn('programmes', 'requirements') && isset($safeProgramme['requirements'])) {
                unset($safeProgramme['requirements']);
            }
            if (!Schema::hasColumn('programmes', 'payment_method') && isset($safeProgramme['payment_method'])) {
                unset($safeProgramme['payment_method']);
            }
            if (!Schema::hasColumn('programmes', 'intake_period') && isset($safeProgramme['intake_period'])) {
                unset($safeProgramme['intake_period']);
            }
            if (!Schema::hasColumn('programmes', 'career_opportunities') && isset($safeProgramme['career_opportunities'])) {
                unset($safeProgramme['career_opportunities']);
            }

            $created = Programme::updateOrCreate(
                ['name' => $programme['name']],
                $safeProgramme
            );

            // 4. Create default variants for each programme
            $variants = [];

            // Full programme variant (pay upfront)
            if ($created->total_price > 0) {
                $variants[] = [
                    'label' => 'Full Payment',
                    'duration' => $created->duration,
                    'total_price' => $created->total_price,
                    'monthly_fee' => 0,
                    'is_active' => true,
                ];
            }

            // Monthly installments variant for paid programmes
            if ($created->total_price > 0 && $created->monthly_fee > 0) {
                $variants[] = [
                    'label' => 'Monthly Installments',
                    'duration' => $created->duration,
                    'total_price' => $created->total_price,
                    'monthly_fee' => $created->monthly_fee,
                    'is_active' => true,
                ];
            }

            // Skip variants for free/short course programmes
            if (!empty($variants)) {
                foreach ($variants as $variant) {
                    ProgrammeVariant::updateOrCreate([
                        'programme_id' => $created->id,
                        'label' => $variant['label'],
                    ], $variant);
                }
            }

            // Seed default modules for each programme
            $this->seedProgrammeModules($created);
        }
    }

    private function seedProgrammeModules(Programme $programme): void
    {
        // Modules are seeded by ModuleSeeder — this method is kept for backwards
        // compatibility only and will be phased out once ModuleSeeder is the norm.
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
            $module = Module::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $moduleData['name'],
                    'description' => $moduleData['description'],
                    'department_id' => $department->id,
                ]
            );

            // Attach via pivot instead of setting programme_id directly
            $programme->modules()->syncWithoutDetaching([
                $module->id => ['order_column' => $index],
            ]);
        }
    }
}
