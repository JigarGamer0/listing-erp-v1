<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Rent', 'description' => 'Office rent and related expenses'],
            ['name' => 'Internet', 'description' => 'Internet and broadband charges'],
            ['name' => 'Electricity', 'description' => 'Electricity bills'],
            ['name' => 'Salary', 'description' => 'Employee salary payments'],
            ['name' => 'Software', 'description' => 'Software licenses and subscriptions'],
            ['name' => 'Marketing', 'description' => 'Marketing and advertising expenses'],
            ['name' => 'Travel', 'description' => 'Travel and conveyance'],
            ['name' => 'Office Supplies', 'description' => 'Stationery and office supplies'],
            ['name' => 'Maintenance', 'description' => 'Equipment and office maintenance'],
            ['name' => 'Other', 'description' => 'Miscellaneous expenses'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
