<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\SetupWizard;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class SetupWizardController extends Controller
{
    public function index()
    {
        if (SetupWizard::isCompleted()) {
            return redirect()->route('login');
        }

        return view('setup.index');
    }

    public function store(Request $request)
    {
        if (SetupWizard::isCompleted()) {
            return redirect()->route('login');
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:100',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_username' => 'required|string|max:100',
            'admin_password' => 'required|string|min:8|confirmed',
            'company_logo' => 'nullable|image|max:2048',
        ]);

        // Run migrations
        Artisan::call('migrate', ['--force' => true]);

        // Run seeders for roles/permissions and expense categories
        Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'ExpenseCategorySeeder', '--force' => true]);

        // Save settings
        Setting::set('company_name', $request->company_name, 'general');
        Setting::set('currency', $request->currency, 'general');
        Setting::set('timezone', $request->timezone, 'general');
        Setting::set('currency_code', 'INR', 'general');
        Setting::set('date_format', 'd/m/Y', 'general');
        Setting::set('default_theme', 'light', 'general');

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('logos', 'public');
            Setting::set('company_logo', $path, 'general', 'file');
        }

        // Create or update admin user
        $admin = User::updateOrCreate(
            ['username' => $request->admin_username],
            [
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'must_change_password' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Main Admin');

        // Mark setup as complete
        SetupWizard::create([
            'completed' => true,
            'completed_at' => now(),
        ]);

        return redirect()->route('login')->with('success', 'Setup completed successfully! Please login with your credentials.');
    }
}
