@extends('layouts.app')
@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee: ' . $employee->name)

@section('content')
<div class="max-w-3xl mx-auto fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Title</label>
                    <input type="text" name="role_title" value="{{ old('role_title', $employee->role_title) }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none" placeholder="e.g. Listing Manager">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                    <select name="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="archived" {{ old('status', $employee->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Salary & Commission</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salary Type *</label>
                    <select name="salary_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="fixed" {{ old('salary_type', $employee->salary_type) === 'fixed' ? 'selected' : '' }}>Fixed</option>
                        <option value="package_based" {{ old('salary_type', $employee->salary_type) === 'package_based' ? 'selected' : '' }}>Package Based</option>
                        <option value="both" {{ old('salary_type', $employee->salary_type) === 'both' ? 'selected' : '' }}>Both (Fixed + Commission)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fixed Salary (₹)</label>
                    <input type="number" name="fixed_salary" value="{{ old('fixed_salary', $employee->fixed_salary) }}" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commission Type *</label>
                    <select name="commission_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="fixed_amount" {{ old('commission_type', $employee->commission_type) === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount (₹ per client)</option>
                        <option value="percentage" {{ old('commission_type', $employee->commission_type) === 'percentage' ? 'selected' : '' }}>Percentage (% of package)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commission Value</label>
                    <input type="number" name="commission_value" value="{{ old('commission_value', $employee->commission_value) }}" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none" placeholder="Amount or percentage">
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">🔑 System Login Credentials</h3>

            @if($employee->user)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username *</label>
                        <input type="text" name="username" value="{{ old('username', $employee->user->username) }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                    </div>
                </div>
            @else
                <div x-data="{ createLogin: {{ old('create_login', 0) }} }">
                    <label class="flex items-center space-x-2 mb-4">
                        <input type="checkbox" name="create_login" value="1" x-model="createLogin" class="w-4 h-4 rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Create system login for this employee</span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5" x-show="createLogin" x-transition>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username *</label>
                            <input type="text" name="username" value="{{ old('username') }}" placeholder="e.g. employee1" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password *</label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('employees.show', $employee) }}" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all">Update Employee</button>
            </div>
        </form>
    </div>
</div>
@endsection
