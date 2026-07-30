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
     * Programme names, durations and fees below are sourced from the
     * official HBCI Fee Structure (2024-2025). Figures not covered by that
     * document (currently: Hospitality Management) are marked TODO and
     * still need confirming against the current fee schedule.
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

        // Rename previously-seeded programmes in place (rather than letting
        // updateOrCreate key on a new name and orphan the old row along with
        // any students/applications already pointing at it). Safe to run
        // against a fresh DB too — non-matching old names are simply no-ops.
        $renames = [
            // Reverting an earlier guess: the fee structure shows this is
            // its own line item named simply "Diploma in Professional Chef".
            'Diploma in Professional Cheffing (Culinary Arts, Italian Cuisine)' => 'Diploma in Professional Chef',
            // Reverting an earlier guess: the fee structure shows "Diploma in
            // Culinary Patisserie" is a standalone 2-year diploma, not a
            // multi-duration bundle — the short course is a separate,
            // separately-priced programme (see "Gastronomy Cooking and
            // Patisserie" below).
            'Patisserie and Baking' => 'Diploma in Culinary Patisserie',
            // The fee structure names the short-course offering "Gastronomy
            // Cooking and Patisserie" with confirmed 3-month/6-month pricing.
            'Short Courses and Cooking Sessions' => 'Gastronomy Cooking and Patisserie',
        ];
        foreach ($renames as $oldName => $newName) {
            Programme::where('name', $oldName)->update(['name' => $newName]);
        }

        // Uniform and tools are flat, institute-wide costs per the fee
        // structure (Uniform: M3,400 — 2x chef pants, 2x chef jackets, apron,
        // chef hat. Tools: M750 — 1x chef's knife, 3x paring knives) and
        // apply the same to every programme below.
        $uniformFee = 3400.00;
        $toolsCost = 750.00;

        $programmes = [
            // Higher Certificate in Contemporary Gastronomy — 1 Year
            [
                'name' => 'Higher Certificate in Contemporary Gastronomy',
                'description' => 'This programme is designed to provide students with foundational knowledge and skills in contemporary gastronomy. Students will learn about food safety, nutrition, culinary techniques, and kitchen operations.',
                'duration' => '1 Year',
                'duration_months' => 12,
                'requirements' => 'LGCSE or JC with at least D in English and Mathematics',
                'payment_method' => 'both',
                'registration_fee' => 2500.00,
                'monthly_fee' => 2570.00,
                'academic_resource_fee' => 1500.00, // "Book Fee" on the fee structure
                'total_price' => 34000.00,
                'uniform_fee' => $uniformFee,
                'tools_cost' => $toolsCost,
                'intake_period' => 'January, April & August',
                'career_opportunities' => 'Junior Chef, Line Cook, Kitchen Assistant, Food Service Associate',
                'department_id' => $contemporaryDept->id,
            ],
            // Diploma in Professional Chef — 3 Years
            [
                'name' => 'Diploma in Professional Chef',
                'description' => 'This comprehensive diploma programme prepares students for a career as a professional chef, with a focus on culinary arts and Italian cuisine. The programme covers advanced cooking techniques, kitchen management, menu planning, and industry placement.',
                'duration' => '3 Years',
                'duration_months' => 36,
                'requirements' => 'LGCSE with at least D in English and Mathematics',
                'payment_method' => 'both',
                'registration_fee' => 3100.00,
                'monthly_fee' => 2570.00,
                'academic_resource_fee' => 5900.00,
                'total_price' => 99000.00,
                'uniform_fee' => $uniformFee,
                'tools_cost' => $toolsCost,
                'intake_period' => 'January only',
                'career_opportunities' => 'Commis Chef, Chef de Partie, Sous Chef, Executive Chef, Kitchen Manager',
                'department_id' => $globalCuisinesDept->id,
            ],
            // Diploma in Culinary Patisserie — 2 Years (standalone diploma; no duration variants)
            [
                'name' => 'Diploma in Culinary Patisserie',
                'description' => 'This programme focuses on the art of patisserie and baking. Students will master classical French pastry techniques, chocolate work, sugar art, and bread making.',
                'duration' => '2 Years',
                'duration_months' => 24,
                'requirements' => 'LGCSE or JC with at least D in English and Mathematics',
                'payment_method' => 'both',
                'registration_fee' => 2500.00,
                'monthly_fee' => 2570.00,
                'academic_resource_fee' => 3800.00,
                'total_price' => 66300.00,
                'uniform_fee' => $uniformFee,
                'tools_cost' => $toolsCost,
                'intake_period' => 'January, April & August',
                'career_opportunities' => 'Pastry Chef, Baker, Confectioner, Cake Designer, Patisserie Manager',
                'department_id' => $patisserieDept->id,
            ],
            // Advanced Diploma in Culinary Arts — 2 Years
            // NOTE: this programme wasn't in the prospectus at all; it only
            // surfaced on the fee structure. Requirements below are a
            // reasonable assumption (LGCSE entry, same as the other
            // programmes) — confirm whether it actually requires prior
            // completion of a related certificate/diploma, and confirm the
            // department (assumed Global Cuisines here, alongside the
            // Diploma in Professional Chef).
            [
                'name' => 'Advanced Diploma in Culinary Arts',
                'description' => 'An advanced culinary arts diploma covering high-level technique, menu development, and kitchen leadership.',
                'duration' => '2 Years',
                'duration_months' => 24,
                'requirements' => 'LGCSE with at least D in English and Mathematics', // TODO: confirm — may require a prior diploma/certificate
                'payment_method' => 'both',
                'registration_fee' => 2500.00,
                'monthly_fee' => 2570.00,
                'academic_resource_fee' => 3800.00,
                'total_price' => 66300.00,
                'uniform_fee' => $uniformFee,
                'tools_cost' => $toolsCost,
                'intake_period' => 'January, April & August', // TODO: confirm intake period
                'career_opportunities' => 'Senior Chef, Culinary Instructor, Kitchen Manager, Executive Sous Chef',
                'department_id' => $globalCuisinesDept->id, // TODO: confirm department
            ],
            // Hospitality Management — 1 Year
            // NOTE: not covered by the fee structure document at all — total
            // price, registration, monthly and book fees below are still
            // unconfirmed placeholders from before. Uniform/tools have been
            // corrected to the confirmed institute-wide flat fees.
            [
                'name' => 'Hospitality Management',
                'description' => 'This programme prepares students for supervisory and management positions in the hospitality industry. Topics include hotel operations, food and beverage management, front office management, and event planning.',
                'duration' => '1 Year',
                'duration_months' => 12,
                'requirements' => 'LGCSE or JC with at least D in English and Mathematics',
                'payment_method' => 'both',
                'registration_fee' => 2800.00, // TODO: not on the fee structure — confirm
                'monthly_fee' => 2500.00, // TODO: not on the fee structure — confirm
                'academic_resource_fee' => 2500.00, // TODO: not on the fee structure — confirm
                'total_price' => 32500.00, // TODO: not on the fee structure — confirm
                'uniform_fee' => $uniformFee,
                'tools_cost' => $toolsCost,
                'intake_period' => 'January, April & August',
                'career_opportunities' => 'Front Office Manager, Food & Beverage Supervisor, Event Coordinator, Hotel Manager',
                'department_id' => $hospitalityDept->id,
            ],
            // Gastronomy Cooking and Patisserie — short course, offered at 3 or 6 months
            [
                'name' => 'Gastronomy Cooking and Patisserie',
                'description' => 'A short, intensive gastronomy and patisserie course for those looking to build practical culinary skills without committing to a full diploma. Offered as a 3-month or 6-month course — see study options below for pricing per duration.',
                'duration' => '3 Months / 6 Months',
                'duration_months' => null,
                'requirements' => 'None required',
                'payment_method' => 'both',
                'registration_fee' => 0.00, // superseded by per-duration ProgrammeVariant pricing below
                'monthly_fee' => 0.00,
                'academic_resource_fee' => 0.00,
                'total_price' => 0.00,
                'uniform_fee' => $uniformFee,
                'tools_cost' => $toolsCost,
                'intake_period' => 'Rolling intake',
                'career_opportunities' => 'Skill enhancement for personal or career development',
                'department_id' => $patisserieDept->id,
                'duration_variants' => [
                    [
                        'label' => 'Gastronomy Cooking and Patisserie (3 Months)',
                        'duration' => '3 Months',
                        'registration_fee' => 1000.00,
                        'monthly_fee' => 2570.00,
                        'academic_resource_fee' => 1500.00,
                        'total_price' => 11500.00,
                    ],
                    [
                        'label' => 'Gastronomy Cooking and Patisserie (6 Months)',
                        'duration' => '6 Months',
                        'registration_fee' => 2000.00,
                        'monthly_fee' => 2570.00,
                        'academic_resource_fee' => 1500.00,
                        'total_price' => 18500.00,
                    ],
                ],
            ],
        ];

        // Insert data
        foreach ($programmes as $programme) {
            // Remove fields that don't exist yet if migration hasn't run
            $safeProgramme = $programme;
            $durationVariants = $safeProgramme['duration_variants'] ?? null;
            unset($safeProgramme['duration_variants']);
            foreach (['uniform_fee', 'tools_cost', 'duration_months', 'requirements', 'payment_method', 'intake_period', 'career_opportunities'] as $optionalColumn) {
                if (!Schema::hasColumn('programmes', $optionalColumn) && isset($safeProgramme[$optionalColumn])) {
                    unset($safeProgramme[$optionalColumn]);
                }
            }

            $created = Programme::updateOrCreate(
                ['name' => $programme['name']],
                $safeProgramme
            );

            if ($durationVariants !== null) {
                // Programme offers several distinct, separately-priced
                // durations (e.g. 3-month vs 6-month course) — each gets its
                // own variant with its own full fee breakdown.
                foreach ($durationVariants as $variant) {
                    $safeVariant = $variant;
                    if (!Schema::hasColumn('programme_variants', 'registration_fee')) {
                        unset($safeVariant['registration_fee']);
                    }
                    if (!Schema::hasColumn('programme_variants', 'academic_resource_fee')) {
                        unset($safeVariant['academic_resource_fee']);
                    }
                    ProgrammeVariant::updateOrCreate([
                        'programme_id' => $created->id,
                        'label' => $variant['label'],
                    ], array_merge($safeVariant, ['is_active' => true]));
                }
            } else {
                // Default payment-plan variants (full payment / monthly) for
                // single-duration programmes, e.g. diplomas.
                $variants = [];

                if ($created->total_price > 0) {
                    $variants[] = [
                        'label' => 'Full Payment',
                        'duration' => $created->duration,
                        'total_price' => $created->total_price,
                        'monthly_fee' => 0,
                        'is_active' => true,
                    ];
                }

                if ($created->total_price > 0 && $created->monthly_fee > 0) {
                    $variants[] = [
                        'label' => 'Monthly Installments',
                        'duration' => $created->duration,
                        'total_price' => $created->total_price,
                        'monthly_fee' => $created->monthly_fee,
                        'is_active' => true,
                    ];
                }

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
