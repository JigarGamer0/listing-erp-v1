<?php

use App\Models\User;
use App\Models\Setting;
use App\Models\SetupWizard;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Completing Setup Wizard...\n";

// 1. Run migrations and seeders
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
\Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);

// 2. Set default settings
Setting::set('company_name', 'Listing ERP', 'general');
Setting::set('currency', '₹', 'general');
Setting::set('timezone', 'Asia/Kolkata', 'general');
Setting::set('currency_code', 'INR', 'general');
Setting::set('date_format', 'd/m/Y', 'general');
Setting::set('default_theme', 'light', 'general');

// 3. Create or update Main Admin user
$admin = User::updateOrCreate(
    ['username' => 'admin'],
    [
        'name' => 'Jigar Patel',
        'email' => 'jigarnaliyadhara10@gmail.com',
        'password' => Hash::make('Jigar@CRM2026#Secure'),
        'must_change_password' => false,
        'status' => 'active',
        'email_verified_at' => now(),
    ]
);

$admin->assignRole('Main Admin');

// 4. Mark setup as complete
SetupWizard::updateOrCreate(
    ['id' => 1],
    [
        'completed' => true,
        'completed_at' => now(),
    ]
);

echo "Setup Wizard Completed Successfully!\n";
