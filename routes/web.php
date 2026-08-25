<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\SetupWizardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPaymentController;
use App\Http\Controllers\ClientAccountController;
use App\Http\Controllers\ClientDocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\FollowUpController;

// ─── Setup Wizard ───────────────────────────────────────────────
Route::get('/setup', [SetupWizardController::class, 'index'])->name('setup.index');
Route::post('/setup', [SetupWizardController::class, 'store'])->name('setup.store');

Route::get('/fix-roles', function() {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Employee']);
    return "Employee role created successfully!";
});

// ─── Authentication ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Force Password Change ─────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'showForm'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'change'])->name('password.change.submit');
});

// ─── Protected Routes ───────────────────────────────────────────
Route::middleware(['auth', 'force.password.change'])->group(function () {

    // Dashboard (Redirects employees internally)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Admin & Main Admin Only Routes ───────────────────────────
    Route::middleware(['role:Main Admin|Admin'])->group(function () {
        Route::post('/fund/clear', [DashboardController::class, 'clearFund'])->name('fund.clear');

        // Clients
        Route::resource('clients', ClientController::class);
        Route::post('/clients/{client}/renew', [ClientController::class, 'renew'])->name('clients.renew');
        Route::post('/clients/{client}/change-package', [ClientController::class, 'changePackage'])->name('clients.change-package');
        Route::post('/clients/{client}/change-gst', [ClientController::class, 'changeGst'])->name('clients.change-gst');
        Route::post('/clients/{client}/change-manager', [ClientController::class, 'changeManager'])->name('clients.change-manager');
        Route::post('/clients/{client}/change-status', [ClientController::class, 'changeStatus'])->name('clients.change-status');
        Route::post('/clients/{client}/add-note', [ClientController::class, 'addNote'])->name('clients.add-note');

        // Client Payments
        Route::get('/payments', [ClientPaymentController::class, 'index'])->name('payments.index');
        Route::get('/clients/{client}/receive-payment', [ClientPaymentController::class, 'create'])->name('payments.create');
        Route::post('/clients/{client}/receive-payment', [ClientPaymentController::class, 'store'])->name('payments.store');

        // Client Accounts
        Route::post('/clients/{client}/accounts', [ClientAccountController::class, 'store'])->name('client-accounts.store');
        Route::put('/clients/{client}/accounts/{account}', [ClientAccountController::class, 'update'])->name('client-accounts.update');
        Route::delete('/clients/{client}/accounts/{account}', [ClientAccountController::class, 'destroy'])->name('client-accounts.destroy');

        // Client Documents
        Route::post('/clients/{client}/documents', [ClientDocumentController::class, 'store'])->name('client-documents.store');
        Route::get('/documents/{document}/download', [ClientDocumentController::class, 'download'])->name('client-documents.download');
        Route::delete('/documents/{document}', [ClientDocumentController::class, 'destroy'])->name('client-documents.destroy');

        // Employees
        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/assign-client', [EmployeeController::class, 'assignClient'])->name('employees.assign-client');
        Route::post('/employees/{employee}/unassign-client/{assignment}', [EmployeeController::class, 'unassignClient'])->name('employees.unassign-client');

        // Salary
        Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
        Route::get('/salary/history', [SalaryController::class, 'history'])->name('salary.history');
        Route::post('/salary/generate', [SalaryController::class, 'generate'])->name('salary.generate');
        Route::get('/salary/preview-salary', [SalaryController::class, 'previewSalary'])->name('salary.preview');
        Route::post('/salary/generate-and-pay', [SalaryController::class, 'generateAndPay'])->name('salary.generate-and-pay');
        Route::post('/salary/pay-quick/{employee}', [SalaryController::class, 'payQuick'])->name('salary.pay-quick');
        Route::post('/employees/{employee}/salary-deductions', [SalaryController::class, 'storeDeduction'])->name('salary.deductions.store');
        Route::delete('/salary-deductions/{deduction}', [SalaryController::class, 'destroyDeduction'])->name('salary.deductions.destroy');
        Route::post('/salary/{salary}/pay', [SalaryController::class, 'pay'])->name('salary.pay');
        Route::get('/salary/advance', [SalaryController::class, 'advanceForm'])->name('salary.advance.form');
        Route::post('/salary/advance', [SalaryController::class, 'processAdvance'])->name('salary.advance');
        Route::post('/salary/advance-requests/{id}/approve', [SalaryController::class, 'approveAdvanceRequest'])->name('salary.advance-requests.approve');
        Route::post('/salary/advance-requests/{id}/reject', [SalaryController::class, 'rejectAdvanceRequest'])->name('salary.advance-requests.reject');
        Route::post('/salary/holiday-requests/{id}/approve', [SalaryController::class, 'approveHolidayRequest'])->name('salary.holiday-requests.approve');
        Route::post('/salary/holiday-requests/{id}/reject', [SalaryController::class, 'rejectHolidayRequest'])->name('salary.holiday-requests.reject');
        Route::get('/admin/advances', [SalaryController::class, 'advances'])->name('admin.advances');
        Route::get('/admin/holidays', [SalaryController::class, 'holidays'])->name('admin.holidays');

        // Expenses
        Route::resource('expenses', ExpenseController::class)->except(['destroy']);
        Route::post('/expenses/{expense}/toggle-calculation', [ExpenseController::class, 'toggleCalculation'])->name('expenses.toggle-calculation');

        // Investors (Master)
        Route::get('/investors', [\App\Http\Controllers\InvestorController::class, 'index'])->name('investors.index');
        Route::post('/investors', [\App\Http\Controllers\InvestorController::class, 'store'])->name('investors.store');
        Route::put('/investors/{investor}', [\App\Http\Controllers\InvestorController::class, 'update'])->name('investors.update');
        Route::get('/investors/api/list', [\App\Http\Controllers\InvestorController::class, 'apiList'])->name('investors.api.list');

        // Investments
        Route::get('/investments', [\App\Http\Controllers\InvestmentController::class, 'index'])->name('investments.index');
        Route::post('/investments', [\App\Http\Controllers\InvestmentController::class, 'store'])->name('investments.store');
        Route::get('/investments/uncleared', [\App\Http\Controllers\InvestmentController::class, 'getUncleared'])->name('investments.uncleared');
        Route::post('/investments/clear', [\App\Http\Controllers\InvestmentController::class, 'clear'])->name('investments.clear');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/collection', [ReportController::class, 'collection'])->name('reports.collection');
        Route::get('/reports/expense', [ReportController::class, 'expense'])->name('reports.expense');
        Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('/reports/client-growth', [ReportController::class, 'clientGrowth'])->name('reports.client-growth');
        Route::get('/reports/salary', [ReportController::class, 'salary'])->name('reports.salary');
        Route::get('/reports/commission', [ReportController::class, 'commission'])->name('reports.commission');
        Route::get('/reports/pending-payments', [ReportController::class, 'pendingPayments'])->name('reports.pending-payments');
        Route::get('/reports/full-report', [ReportController::class, 'fullReport'])->name('reports.full-report');
        Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // User Management (Main Admin Only)
        Route::middleware(['role:Main Admin'])->group(function () {
            Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');
            Route::post('/settings/users', [SettingsController::class, 'createUser'])->name('settings.users.create');
            Route::put('/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
        });

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // ─── Employee & Common Authenticated Routes ──────────────────
    // Work Tracker (accessible by both Employee and Admin)
    Route::get('/work-tracker', [\App\Http\Controllers\WorkTrackerController::class, 'index'])->name('work-tracker.index');
    Route::get('/work-tracker/monthly-history', [\App\Http\Controllers\WorkTrackerController::class, 'monthlyHistory'])->name('work-tracker.monthly-history');

    // Employee Dashboard & Sub-modules (Employee Only)
    Route::middleware(['role:Employee'])->group(function () {
        Route::get('/employee/dashboard', [\App\Http\Controllers\EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
        Route::get('/employee/clients', [\App\Http\Controllers\EmployeeDashboardController::class, 'clients'])->name('employee.clients');
        Route::get('/employee/salaries', [\App\Http\Controllers\EmployeeDashboardController::class, 'salaries'])->name('employee.salaries');
        Route::get('/employee/advances', [\App\Http\Controllers\EmployeeDashboardController::class, 'advances'])->name('employee.advances');
        Route::get('/employee/holidays', [\App\Http\Controllers\EmployeeDashboardController::class, 'holidays'])->name('employee.holidays');
        Route::post('/employee/daily-work', [\App\Http\Controllers\EmployeeDashboardController::class, 'storeDailyWorkLog'])->name('employee.daily-work');
        Route::post('/employee/advance-request', [\App\Http\Controllers\EmployeeDashboardController::class, 'storeAdvanceRequest'])->name('employee.advance-request');
        Route::post('/employee/holiday-request', [\App\Http\Controllers\EmployeeDashboardController::class, 'storeHolidayRequest'])->name('employee.holiday-request');
    });

    // Follow Ups
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::put('/follow-ups/{followUp}', [FollowUpController::class, 'update'])->name('follow-ups.update');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});
