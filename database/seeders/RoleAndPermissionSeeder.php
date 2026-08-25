<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Client permissions
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'clients.change_package', 'clients.change_gst', 'clients.change_manager',
            'clients.view_accounts', 'clients.manage_accounts',
            'clients.view_documents', 'clients.manage_documents',
            'clients.view_notes', 'clients.manage_notes',
            'clients.view_timeline',

            // Payment permissions
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',

            // Employee permissions
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'employees.assign_clients',

            // Salary permissions
            'salary.view', 'salary.generate', 'salary.pay', 'salary.advance',

            // Expense permissions
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',

            // Report permissions
            'reports.view', 'reports.export',

            // Activity log permissions
            'activity_logs.view',

            // Settings permissions
            'settings.view', 'settings.edit',
            'users.view', 'users.create', 'users.edit', 'users.delete',

            // Notification permissions
            'notifications.view',

            // Follow-up permissions
            'follow_ups.view', 'follow_ups.create', 'follow_ups.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Main Admin role with all permissions
        $mainAdmin = Role::firstOrCreate(['name' => 'Main Admin']);
        $mainAdmin->givePermissionTo(Permission::all());

        // Create Admin role with limited permissions
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([
            'clients.view', 'clients.create', 'clients.edit',
            'clients.change_package', 'clients.change_gst', 'clients.change_manager',
            'clients.view_accounts', 'clients.manage_accounts',
            'clients.view_documents', 'clients.manage_documents',
            'clients.view_notes', 'clients.manage_notes',
            'clients.view_timeline',
            'payments.view', 'payments.create', 'payments.edit',
            'employees.view', 'employees.create', 'employees.edit',
            'employees.assign_clients',
            'salary.view', 'salary.generate', 'salary.pay', 'salary.advance',
            'expenses.view', 'expenses.create', 'expenses.edit',
            'reports.view', 'reports.export',
            'notifications.view',
            'follow_ups.view', 'follow_ups.create', 'follow_ups.edit',
        ]);

        // Create Employee role
        Role::firstOrCreate(['name' => 'Employee']);
    }
}
