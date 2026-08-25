<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class EmployeeTestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Employee role exists
        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);

        // Create user
        $user = User::firstOrCreate(
            ['username' => 'test1'],
            [
                'name' => 'Test Employee',
                'email' => 'test1@listingerp.com',
                'username' => 'test1',
                'password' => Hash::make('Test@1'),
                'must_change_password' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('Employee');

        // Create Employee profile if not exists
        Employee::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Test Employee',
                'phone' => '9999999999',
                'joining_date' => now()->format('Y-m-d'),
                'role_title' => 'Software Engineer',
                'salary_type' => 'both', // Both fixed salary and commission base
                'fixed_salary' => 15000,
                'commission_type' => 'percentage',
                'commission_value' => 10, // 10%
                'status' => 'active',
            ]
        );
    }
}
