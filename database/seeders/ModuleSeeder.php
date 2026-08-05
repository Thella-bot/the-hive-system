<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\Programme;
use App\Models\Department;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureProgrammeModuleColumns();

        $depts = [
            'hospitality-management'   => Department::where('slug', 'hospitality-management')->first(),
            'patisseries'               => Department::where('slug', 'patisseries')->first(),
            'food-safety'               => Department::where('slug', 'food-safety')->first(),
            'global-cuisines'           => Department::where('slug', 'global-cuisines')->first(),
            'contemporary-gastronomy'  => Department::where('slug', 'contemporary-gastronomy')->first(),
        ];


        $this->seedRealCurriculum($depts);


        $shared = $this->sharedModules($depts);
        $byDepartment = $this->departmentModules($depts);
        $this->attachPlaceholders();
    }

    /**
     * Create a batch of modules from [code => [name, topics]] definitions.
     */
    private function createModules(array $defs, string $type, ?int $departmentId, int $credits = 0): array
    {
        $created = [];
        foreach ($defs as $def) {
            $description = !empty($def['topics'])
                ? implode("\n", array_map(fn($t) => "• {$t}", $def['topics']))
                : null;

            $created[$def['code']] = Module::updateOrCreate(
                ['code' => $def['code']],
                [
                    'name'          => $def['name'],
                    'description'   => $description,
                    'type'          => $type,
                    'credits'       => $credits,
                    'department_id' => $departmentId,
                ]
            );
        }
        return $created;
    }

    private function ensureProgrammeModuleColumns(): void
    {
        if (!Schema::hasTable('programme_module')) {
            return;
        }

        Schema::table('programme_module', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_module', 'year_level')) {
                $table->unsignedTinyInteger('year_level')->nullable();
            }

            if (!Schema::hasColumn('programme_module', 'semester')) {
                $table->unsignedTinyInteger('semester')->nullable();
            }
        });
    }

    /**
     * Attach modules to a programme at a given year/semester.
     * Detaches any existing modules for that year/semester first (clean slate).
     */
    private function attachAt(string $programmeName, $programmes, array $codes, ?int $year, ?int $semester): void
    {
        $programmeId = $programmes[$programmeName] ?? null;
        if (!$programmeId) return;

        $programme = Programme::find($programmeId);

        $pivotQuery = $programme->modules()->wherePivot('year_level', $year);
        if ($semester !== null) {
            $pivotQuery->wherePivot('semester', $semester);
        } else {
            $pivotQuery->whereNull('programme_module.semester');
        }

        $existing = $pivotQuery->pluck('module_id');
        if ($existing->isNotEmpty()) {
            $programme->modules()->detach($existing);
        }

        $modules = Module::whereIn('code', $codes)->get()->keyBy('code');
        $syncData = [];
        foreach ($codes as $order => $code) {
            if (!isset($modules[$code])) continue;
            $syncData[$modules[$code]->id] = [
                'order_column' => $order,
                'year_level'   => $year,
                'semester'     => $semester,
            ];
        }

        $programme->modules()->syncWithoutDetaching($syncData);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  REAL CURRICULUM (3‑year professional chef)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedRealCurriculum(array $depts): void
    {
        $hmId  = $depts['hospitality-management']?->id;
        $ptId  = $depts['patisseries']?->id;
        $fsId  = $depts['food-safety']?->id;
        $gcId  = $depts['global-cuisines']?->id;
        $cgId  = $depts['contemporary-gastronomy']?->id;

        // ---- create all modules -------------------------------------------------
        $allModules = array_merge(
            $this->createModules($this->hmModules(), 'knowledge', $hmId),
            $this->createModules($this->ptModules(), 'knowledge', $ptId),
            $this->createModules($this->fsModules(), 'knowledge', $fsId),
            $this->createModules($this->gcModules(), 'knowledge', $gcId),
            $this->createModules($this->cgModules(), 'knowledge', $cgId),
        );

        $programmes = Programme::pluck('id', 'name');

        // ---- Year 1 modules grouped by semester -------------------------------
        $y1s1Codes = [ // Semester 1
            'HM101',
            'HM102',
            'HM103',
            'HM104',
            'HM105',
            'HM106',
            'HM107',
            'HM109',
            'HM110',
            'HM111',
            'HM112',
            'HM113',
            'FS101',
            'FS102',
            'CG101',
            'CG102',
            'CG103',
            'CG104',
            'PT101',
            'GC101',
        ];
        $y1s2Codes = [ // Semester 2
            'HM108',
            'PT102',
            'PT103',
            'PT104',
            'PT105',
            'PT106',
            'FS103',
            'FS104',
            'GC102',
            'GC103',
            'GC104',
            'GC105',
            'GC106',
            'GC107',
            'GC108',
            'GC109',
            'GC110',
            'GC111',
        ];
        $y1AllCodes = array_merge($y1s1Codes, $y1s2Codes);

        // ---- Year 2 modules ---------------------------------------------------
        $y2s1Codes = [
            'HM202',
            'FS201',
            'FS202',
            'GC201',
            'GC202',
            'GC203',
            'GC204',
            'GC205',
            'GC206',
            'GC207',
            'GC208',
            'GC209',
            'PT201',
            'PT202',
            'PT203',
            'PT204',
            'PT205',
            'CG201',
        ];
        // Year 2 Semester 2 was internship only – now removed.

        // ---- Year 3 modules ---------------------------------------------------
        $y3s1Codes = [
            'HM203',
            'HM204',
            'HM205',
            'HM206',
            'HM207',
            'HM208',
            'HM209',
            'HM210',
            'FS301',
            'FS302',
            'GC301',
            'GC302',
            'GC303',
            'GC304',
            'GC305',
            'CG301',
            'CG302',
        ];
        // Year 3 Semester 2 was internship only – now removed.

        // ---- Attach to programmes --------------------------------------------

        // 1‑year Higher Certificate (full Year 1)
        $this->attachAt('Higher Certificate in Contemporary Gastronomy', $programmes, $y1AllCodes, 1, null);

        // Short course (same Year 1 material)
        $this->attachAt('Gastronomy Cooking and Patisserie', $programmes, $y1AllCodes, 1, null);

        // 3‑year Diploma – full progression (internship semesters removed)
        $this->attachAt('Diploma in Professional Chef', $programmes, $y1s1Codes, 1, 1);
        $this->attachAt('Diploma in Professional Chef', $programmes, $y1s2Codes, 1, 2);
        $this->attachAt('Diploma in Professional Chef', $programmes, $y2s1Codes, 2, 1);
        $this->attachAt('Diploma in Professional Chef', $programmes, $y3s1Codes, 3, 1);
    }

    // ── Module definitions (code, name) ──────────────────────────────────────
    private function hmModules(): array
    {
        return [
            ['code' => 'HM101', 'name' => 'Introduction to Professional Cookery'],
            ['code' => 'HM102', 'name' => 'Kitchen Inventory and Stock Control'],
            ['code' => 'HM103', 'name' => 'Food Production Planning'],
            ['code' => 'HM104', 'name' => 'Kitchen Equipment and Preparation Techniques'],
            ['code' => 'HM105', 'name' => 'Basic Ingredients and Commodities'],
            ['code' => 'HM106', 'name' => 'Restaurant Service'],
            ['code' => 'HM107', 'name' => 'Beverage Management'],
            ['code' => 'HM108', 'name' => 'Menu Planning and Recipe Costing I'],
            ['code' => 'HM109', 'name' => 'Computer Literacy for Hospitality'],
            ['code' => 'HM110', 'name' => 'Numeracy and Measurement for Chefs'],
            ['code' => 'HM111', 'name' => 'Personal Development as a Chef'],
            ['code' => 'HM112', 'name' => 'Environmental Awareness and Sustainability'],
            ['code' => 'HM113', 'name' => 'Workplace Safety and Risk Management'],
            ['code' => 'HM202', 'name' => 'Menu Planning and Recipe Costing II'],
            ['code' => 'HM203', 'name' => 'Food Production Management'],
            ['code' => 'HM204', 'name' => 'Introduction to Management'],
            ['code' => 'HM205', 'name' => 'Theory of Food Production'],
            ['code' => 'HM206', 'name' => 'Production Facility and Equipment Resource Management'],
            ['code' => 'HM207', 'name' => 'Commodity Resource Management'],
            ['code' => 'HM208', 'name' => 'Theory of Staff Resource Management'],
            ['code' => 'HM209', 'name' => 'Menu Planning and Implementation'],
            ['code' => 'HM210', 'name' => 'Operational Cost Control'],

        ];
    }

    private function ptModules(): array
    {
        return [
            ['code' => 'PT101', 'name' => 'Introduction to Patisserie'],
            ['code' => 'PT102', 'name' => 'Baking Science and Fermented Doughs I'],
            ['code' => 'PT103', 'name' => 'Cakes, Biscuits and Sponge Products I'],
            ['code' => 'PT104', 'name' => 'Hot and Cold Desserts and Puddings I'],
            ['code' => 'PT105', 'name' => 'Pastry-Based Products I'],
            ['code' => 'PT106', 'name' => 'Chocolate and Petit Fours I'],
            ['code' => 'PT201', 'name' => 'Baking Science and Fermented Doughs II'],
            ['code' => 'PT202', 'name' => 'Cakes, Biscuits and Sponge Products II'],
            ['code' => 'PT203', 'name' => 'Hot and Cold Desserts and Puddings II'],
            ['code' => 'PT204', 'name' => 'Pastry-Based Products II'],
            ['code' => 'PT205', 'name' => 'Chocolate and Petit Fours II'],
        ];
    }

    private function fsModules(): array
    {
        return [
            ['code' => 'FS101', 'name' => 'Food Safety and Quality Assurance'],
            ['code' => 'FS102', 'name' => 'Personal Hygiene and Safety'],
            ['code' => 'FS103', 'name' => 'Kitchen Safety Management I'],
            ['code' => 'FS104', 'name' => 'Nutrition and Healthier Food Preparation I'],
            ['code' => 'FS201', 'name' => 'Kitchen Safety Management II'],
            ['code' => 'FS202', 'name' => 'Nutrition and Healthier Food Preparation II'],
            ['code' => 'FS301', 'name' => 'Kitchen Safety Management III'],
            ['code' => 'FS302', 'name' => 'Theory of Safety Supervision'],
        ];
    }

    private function gcModules(): array
    {
        return [
            ['code' => 'GC101', 'name' => 'Introduction to Garde Manger'],
            ['code' => 'GC102', 'name' => 'Salads, Cold Sauces and Dressings I'],
            ['code' => 'GC103', 'name' => 'Sandwiches and Cheese I'],
            ['code' => 'GC104', 'name' => 'Charcuterie and Preservation I'],
            ['code' => 'GC105', 'name' => 'Classic Hors d\'oeuvres and Starters I'],
            ['code' => 'GC106', 'name' => 'Vegetable, Pulse and Fruit Cookery I'],
            ['code' => 'GC107', 'name' => 'Stocks, Soups and Sauces I'],
            ['code' => 'GC108', 'name' => 'Pasta, Rice and Grain Dishes I'],
            ['code' => 'GC109', 'name' => 'Meat, Offal and Poultry I'],
            ['code' => 'GC110', 'name' => 'Fish and Seafood Cookery I'],
            ['code' => 'GC111', 'name' => 'Introduction to Global Cuisines'],
            ['code' => 'GC201', 'name' => 'Salads, Cold Sauces and Dressings II'],
            ['code' => 'GC202', 'name' => 'Sandwiches and Cheese II'],
            ['code' => 'GC203', 'name' => 'Charcuterie and Preservation II'],
            ['code' => 'GC204', 'name' => 'Classic Hors d\'oeuvres and Starters II'],
            ['code' => 'GC205', 'name' => 'Vegetable, Pulse and Fruit Cookery II'],
            ['code' => 'GC206', 'name' => 'Stocks, Soups and Sauces II'],
            ['code' => 'GC207', 'name' => 'Pasta, Rice and Grain Dishes II'],
            ['code' => 'GC208', 'name' => 'Meat, Offal and Poultry II'],
            ['code' => 'GC209', 'name' => 'Fish and Seafood Cookery II'],
            ['code' => 'GC301', 'name' => 'Cuisines of Asia'],
            ['code' => 'GC302', 'name' => 'Cuisines of Europe'],
            ['code' => 'GC303', 'name' => 'Cuisines of the Americas'],
            ['code' => 'GC304', 'name' => 'New African Cuisine: Northern & Horn of Africa'],
            ['code' => 'GC305', 'name' => 'New African Cuisine: Sub-Saharan Africa'],
        ];
    }

    private function cgModules(): array
    {
        return [
            ['code' => 'CG101', 'name' => 'History of Food and Cooking'],
            ['code' => 'CG102', 'name' => 'Herbs, Spices and Flavourings'],
            ['code' => 'CG103', 'name' => 'Principles of Flavour and Essence of Ingredients'],
            ['code' => 'CG104', 'name' => 'Gastronomy and Flavour Science I'],
            ['code' => 'CG201', 'name' => 'Gastronomy and Flavour Science II'],
            ['code' => 'CG301', 'name' => 'Modernist Cuisine'],
            ['code' => 'CG302', 'name' => 'Advanced Gastronomy'],
        ];
    }
    private function sharedModules(array $depts): array
    {
        $modules = [
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

            'PB101' => ['name' => 'Baking Science & Ingredients',                'credits' => 15, 'dept' => 'patisseries'],
            'PB102' => ['name' => 'Classical French Pastry',                      'credits' => 15, 'dept' => 'patisseries'],
            'PB103' => ['name' => 'Artisan Bread Making',                         'credits' => 15, 'dept' => 'patisseries'],
            'PB104' => ['name' => 'Cake Decorating & Sugar Art',                  'credits' => 15, 'dept' => 'patisseries'],
            'PB105' => ['name' => 'Chocolate Craft & Confectionery',              'credits' => 15, 'dept' => 'patisseries'],
            'PB106' => ['name' => 'Frozen Desserts & Ice Cream',                  'credits' => 10, 'dept' => 'patisseries'],
            'PB107' => ['name' => 'Petits Fours & Fine Baking',                   'credits' => 10, 'dept' => 'patisseries'],
            'PB108' => ['name' => 'Entrepreneurial Baking',                        'credits' => 10, 'dept' => 'patisseries'],
        ];

        $created = [];
        foreach ($modules as $code => $data) {
            $dept = $depts[$data['dept']] ?? null;
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
        $templates = [
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

    private function attachPlaceholders(): void
    {
        $programmes = Programme::pluck('id', 'name');

        // Diploma in Culinary Patisserie — TODO: replace with real outline
        $this->attach('Diploma in Culinary Patisserie', $programmes, [
            'PB101',
            'PB102',
            'PB103',
            'PB104',
            'PB105',
            'PB106',
            'PB107',
            'PB108',
            'PB201',
            'PB202',
            'PB203',
        ]);

        // Hospitality Management — TODO: replace with real outline
        $this->attach('Hospitality Management', $programmes, [
            'HM101',
            'HM102',
            'HM103',
            'HM104',
            'HM105',
            'HM106',
            'HM107',
            'HM108',
            'HM109',
            'HM110',
            'HM201',
            'HM202',
        ]);
    }

    private function attach(string $programmeName, $programmes, array $codes): void
    {
        $programmeId = $programmes[$programmeName] ?? null;
        if (!$programmeId) return;

        $moduleIds = Module::whereIn('code', $codes)->pluck('id');

        $syncData = [];
        foreach ($moduleIds as $id) {
            $syncData[$id] = ['order_column' => array_search(Module::where('id', $id)->value('code'), $codes)];
        }

        Programme::find($programmeId)->modules()->syncWithoutDetaching($syncData);
    }
}
