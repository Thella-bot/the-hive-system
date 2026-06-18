<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Office Supplies', 'code' => 'OFF', 'description' => 'Stationery, pens, paper, and office materials'],
            ['name' => 'Equipment', 'code' => 'EQP', 'description' => 'Kitchen equipment, tools, and machinery'],
            ['name' => 'Maintenance', 'code' => 'MNT', 'description' => 'Repairs and maintenance costs'],
            ['name' => 'Utilities', 'code' => 'UTL', 'description' => 'Electricity, water, internet, and phone'],
            ['name' => 'Cleaning', 'code' => 'CLN', 'description' => 'Cleaning services and supplies'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Advertising, promotions, and marketing materials'],
            ['name' => 'Training', 'code' => 'TRN', 'description' => 'Staff training and development'],
            ['name' => 'Uniforms', 'code' => 'UNF', 'description' => 'Staff uniforms and protective clothing'],
            ['name' => 'Ingredients', 'code' => 'ING', 'description' => 'Food ingredients and raw materials'],
            ['name' => 'Licenses', 'code' => 'LIC', 'description' => 'Software licenses and permits'],
            ['name' => 'Insurance', 'code' => 'INS', 'description' => 'Business insurance premiums'],
            ['name' => 'Travel', 'code' => 'TRV', 'description' => 'Travel and accommodation expenses'],
            ['name' => 'Catering', 'code' => 'CAT', 'description' => 'Catering for events and functions'],
            ['name' => 'Professional Fees', 'code' => 'PRF', 'description' => 'Legal, accounting, and consulting fees'],
            ['name' => 'Miscellaneous', 'code' => 'MSC', 'description' => 'Other miscellaneous expenses'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }
    }
}