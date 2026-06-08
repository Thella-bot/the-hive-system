<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Programme;
use App\Models\Department;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $depts = [
            'hospitality-management' => Department::where('slug', 'hospitality-management')->first(),
            'patisseries'             => Department::where('slug', 'patisseries')->first(),
            'contemporary-gastronomy'=> Department::where('slug', 'contemporary-gastronomy')->first(),
            'global-cuisines'         => Department::where('slug', 'global-cuisines')->first(),
        ];

        // ── Shared kitchen modules (can be reused across programmes) ────────────
        $shared = $this->sharedModules($depts);

        // ── Department-specific modules ─────────────────────────────────────────
        $byDepartment = $this->departmentModules($depts);

        // ── Attach modules to programmes via pivot ───────────────────────────────
        $this->attachToProgrammes();
    }

    private function sharedModules(array $depts): array
    {
        $modules = [
            // Hospitality Management shared modules
            'HM101' => ['name' => 'Introduction to Hospitality Industry',         'credits' => 10, 'dept' => 'hospitality-management'],
            'HM102' => ['name' => 'Food Safety & Hygiene Certification',         'credits' => 15, 'dept' => 'hospitality-management'],
            'HM103' => ['name' => 'Customer Service Excellence',                 'credits' => 10, 'dept' => 'hospitality-management'],
            'HM104' => ['name' => 'Hotel Front Office Operations',                 'credits' => 15, 'dept' => 'hospitality-management'],
            'HM105' => ['name' => 'Food & Beverage Operations',                    'credits' => 15, 'dept' => 'hospitality-management'],
            'HM106' => ['name' => 'Housekeeping & Accommodation Services',         'credits' => 10, 'dept' => 'hospitality-management'],
            'HM107' => ['name' => 'Hospitality Accounting & Cost Control',         'credits' => 10, 'dept' => 'hospitality-management'],
            'HM108' => ['name' => 'Human Resource Management in Hospitality',     'credits' => 10, 'dept' => 'hospitality-management'],
            'HM109' => ['name' => 'Event Planning & Banqueting',                  'credits' => 15, 'dept' => 'hospitality-management'],
            'HM110' => ['name' => 'Entrepreneurship in Hospitality',               'credits' => 10, 'dept' => 'hospitality-management'],

            // Contemporary Gastronomy shared modules
            'CG101' => ['name' => 'Principles of Culinary Arts',                  'credits' => 15, 'dept' => 'contemporary-gastronomy'],
            'CG102' => ['name' => 'Knife Skills & Vegetable Cuts',                'credits' => 10, 'dept' => 'contemporary-gastronomy'],
            'CG103' => ['name' => 'Stocks, Sauces & Soups',                       'credits' => 15, 'dept' => 'contemporary-gastronomy'],
            'CG104' => ['name' => 'Meat, Poultry & Game Fabrication',            'credits' => 15, 'dept' => 'contemporary-gastronomy'],
            'CG105' => ['name' => 'Seafood Processing & Cookery',                 'credits' => 15, 'dept' => 'contemporary-gastronomy'],
            'CG106' => ['name' => 'Foundations of Baking & Pastry',               'credits' => 10, 'dept' => 'contemporary-gastronomy'],
            'CG107' => ['name' => 'Cold Kitchen & Larder',                        'credits' => 15, 'dept' => 'contemporary-gastronomy'],
            'CG108' => ['name' => 'Nutrition & Healthier Cooking',                'credits' => 10, 'dept' => 'contemporary-gastronomy'],
            'CG109' => ['name' => 'Plating & Presentation Techniques',            'credits' => 10, 'dept' => 'contemporary-gastronomy'],
            'CG110' => ['name' => 'Kitchen Operations & Management',              'credits' => 15, 'dept' => 'contemporary-gastronomy'],

            // Patisserie shared modules
            'PB101' => ['name' => 'Baking Science & Ingredients',                'credits' => 15, 'dept' => 'patisseries'],
            'PB102' => ['name' => 'Classical French Pastry',                      'credits' => 15, 'dept' => 'patisseries'],
            'PB103' => ['name' => 'Artisan Bread Making',                         'credits' => 15, 'dept' => 'patisseries'],
            'PB104' => ['name' => 'Cake Decorating & Sugar Art',                  'credits' => 15, 'dept' => 'patisseries'],
            'PB105' => ['name' => 'Chocolate Craft & Confectionery',              'credits' => 15, 'dept' => 'patisseries'],
            'PB106' => ['name' => 'Frozen Desserts & Ice Cream',                  'credits' => 10, 'dept' => 'patisseries'],
            'PB107' => ['name' => 'Petits Fours & Fine Baking',                   'credits' => 10, 'dept' => 'patisseries'],
            'PB108' => ['name' => 'Entrepreneurial Baking',                        'credits' => 10, 'dept' => 'patisseries'],

            // Global Cuisines shared modules
            'GC101' => ['name' => 'Culinary Foundations & Classical Techniques', 'credits' => 15, 'dept' => 'global-cuisines'],
            'GC102' => ['name' => 'Professional Knife Skills',                    'credits' => 10, 'dept' => 'global-cuisines'],
            'GC103' => ['name' => 'African Heritage Cuisine',                     'credits' => 15, 'dept' => 'global-cuisines'],
            'GC104' => ['name' => 'Asian Culinary Traditions',                    'credits' => 15, 'dept' => 'global-cuisines'],
            'GC105' => ['name' => 'European Classical Cuisine',                   'credits' => 15, 'dept' => 'global-cuisines'],
            'GC106' => ['name' => 'Menu Planning & Costing',                     'credits' => 10, 'dept' => 'global-cuisines'],
            'GC107' => ['name' => 'Food Photography & Styling',                   'credits' => 10, 'dept' => 'global-cuisines'],
            'GC108' => ['name' => 'Research & Development in Culinary Arts',     'credits' => 10, 'dept' => 'global-cuisines'],
        ];

        $created = [];
        foreach ($modules as $code => $data) {
            $dept = $depts[$data['dept']];
            $created[$code] = Module::updateOrCreate(
                ['code' => $code],
                [
                    'name'         => $data['name'],
                    'credits'      => $data['credits'],
                    'department_id' => $dept?->id,
                ]
            );
        }

        return $created;
    }

    private function departmentModules(array $depts): array
    {
        // Additional department-specific modules (also reusable across programmes)
        $templates = [
            'contemporary-gastronomy' => [
                'CG201' => ['name' => 'Modernist Cuisine & Molecular Techniques', 'credits' => 15],
                'CG202' => ['name' => 'Pastry & Confectionery for Chefs',        'credits' => 10],
                'CG203' => ['name' => 'Butchery & Charcuterie',                 'credits' => 15],
            ],
            'global-cuisines' => [
                'GC201' => ['name' => 'Mediterranean Cuisine',                   'credits' => 15],
                'GC202' => ['name' => 'Latin American Cuisine',                  'credits' => 15],
                'GC203' => ['name' => 'Japanese & East Asian Cuisine',           'credits' => 15],
                'GC204' => ['name' => 'Indian Subcontinent Cuisine',            'credits' => 15],
                'GC205' => ['name' => 'Beverage Studies & Mixology',            'credits' => 10],
            ],
            'patisseries' => [
                'PB201' => ['name' => 'Wedding Cakes & Tiered Designs',          'credits' => 15],
                'PB202' => ['name' => 'Chocolate Tempering & Sculpting',         'credits' => 15],
                'PB203' => ['name' => 'Plated Dessert Composition',             'credits' => 10],
            ],
            'hospitality-management' => [
                'HM201' => ['name' => 'Leadership in Hospitality',               'credits' => 15],
                'HM202' => ['name' => 'Digital Marketing for Hospitality',       'credits' => 10],
            ],
        ];

        $created = [];
        foreach ($templates as $deptSlug => $modules) {
            $dept = $depts[$deptSlug] ?? null;
            foreach ($modules as $code => $data) {
                $created[$code] = Module::updateOrCreate(
                    ['code' => $code],
                    [
                        'name'           => $data['name'],
                        'credits'        => $data['credits'],
                        'department_id'  => $dept?->id,
                    ]
                );
            }
        }

        return $created;
    }

    private function attachToProgrammes(): void
    {
        $programmes = Programme::pluck('id', 'name');

        // Higher Certificate in Contemporary Gastronomy
        $this->attach('Higher Certificate in Contemporary Gastronomy', $programmes, [
            'CG101', 'CG102', 'CG103', 'CG104', 'CG105',
            'CG106', 'CG107', 'CG108', 'CG201', 'CG202', 'CG203',
        ]);

        // Diploma in Professional Chef
        $this->attach('Diploma in Professional Chef', $programmes, [
            'GC101', 'GC102', 'GC103', 'GC104', 'GC105',
            'GC106', 'CG103', 'CG104', 'CG105',
            'GC201', 'GC202', 'GC203', 'GC204', 'GC205',
        ]);

        // Diploma in Culinary Patisserie
        $this->attach('Diploma in Culinary Patisserie', $programmes, [
            'PB101', 'PB102', 'PB103', 'PB104', 'PB105',
            'PB106', 'PB107', 'PB108',
            'PB201', 'PB202', 'PB203',
        ]);

        // Hospitality Management
        $this->attach('Hospitality Management', $programmes, [
            'HM101', 'HM102', 'HM103', 'HM104', 'HM105',
            'HM106', 'HM107', 'HM108', 'HM109', 'HM110',
            'HM201', 'HM202',
        ]);

        // Short Courses and Cooking Sessions — no modules needed
    }

    private function attach(string $programmeName, $programmes, array $codes): void
    {
        $programmeId = $programmes[$programmeName] ?? null;
        if (!$programmeId) return;

        $moduleIds = Module::whereIn('code', $codes)->pluck('id');

        // Sync to avoid duplicates
        $syncData = [];
        foreach ($moduleIds as $id) {
            $syncData[$id] = ['order_column' => array_search(Module::where('id', $id)->value('code'), $codes)];
        }

        Programme::find($programmeId)->modules()->syncWithoutDetaching($syncData);
    }
}
