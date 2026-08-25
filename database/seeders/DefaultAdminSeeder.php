<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Main Admin',
                'email' => 'admin@listingerp.com',
                'username' => 'admin',
                'password' => Hash::make('Admin@123'),
                'must_change_password' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Main Admin');
    }
}
