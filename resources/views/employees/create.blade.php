@extends('layouts.app')
@section('title', 'Add Employee')
@section('page-title', 'Add New Employee')

@section('content')
<div class="max-w-3xl mx-auto fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label><input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label><input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Joining Date *</label><input type="date" name="joining_date" value="{{ old('joining_date', date('Y-m-d')) }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Title</label><input type="text" name="role_title" value="{{ old('role_title') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none" placeholder="e.g. Listing Manager"></div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Salary & Commission</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salary Type *</label><select name="salary_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"><option value="fixed">Fixed</option><option value="package_based">Package Based</option><option value="both">Both (Fixed + Commission)</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fixed Salary (₹)</label><input type="number" name="fixed_salary" value="{{ old('fixed_salary', 0) }}" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commission Type *</label><select name="commission_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"><option value="fixed_amount">Fixed Amount (₹ per client)</option><option value="percentage">Percentage (% of package)</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commission Value</label><input type="number" name="commission_value" value="{{ old('commission_value', 0) }}" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none" placeholder="Amount or percentage"></div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Login Account (Optional)</h3>
            <div x-data="{ createLogin: false }">
                <label class="flex items-center space-x-2 mb-4"><input type="checkbox" name="create_login" value="1" x-model="createLogin" class="w-4 h-4 rounded border-gray-300 text-blue-600"><span class="text-sm text-gray-700 dark:text-gray-300">Create system login for this employee</span></label>
                <div x-show="createLogin" x-transition class="grid grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label><input type="text" name="username" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label><input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none"></div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('employees.index') }}" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all">Create Employee</button>
            </div>
        </form>
    </div>
</div>
@endsection
