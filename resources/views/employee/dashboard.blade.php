@extends('layouts.app')
@section('title', 'Employee Dashboard — Listing ERP')
@section('page-title', 'My Dashboard')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Welcome, {{ $employee->name }}</h2>
        <p class="text-sm text-gray-500">Quick overview of your active assignments and balances.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-8">
        {{-- Card 1: Assigned Clients Count --}}
        <a href="{{ route('employee.clients') }}" class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs text-gray-400 uppercase font-medium">Assigned Clients</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ count($clientDetails) }}</p>
            <span class="text-xs text-blue-600 font-semibold block mt-3">View Clients List →</span>
        </a>

        {{-- Card 2: Salary Structure Mode --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-medium">Salary Base</p>
            <p class="text-xl font-bold text-indigo-600 mt-1.5">
                @if($employee->salary_type === 'salary')
                    Fixed Salary Base
                @elseif($employee->salary_type === 'commission')
                    Percentage/Commission Base
                @else
                    Salary + Commission Base
                @endif
            </p>
        </div>

        {{-- Card 3: Base / Expected Earnings --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-medium">Monthly Compensation</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1.5">₹{{ number_format($grossEarnings, 2) }}</p>
        </div>

        {{-- Card 4: Pending Advances --}}
        <a href="{{ route('employee.advances') }}" class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs text-gray-400 uppercase font-medium">Outstanding Advance Balance</p>
            <p class="text-2xl font-bold text-red-650 mt-1.5">₹{{ number_format($pendingAdvanceBalance, 2) }}</p>
            <span class="text-xs text-red-650 font-semibold block mt-3">View Ledger History →</span>
        </a>

        {{-- Card 5: Net Payout --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-medium font-bold text-green-600 dark:text-green-400">Net Expected Payout</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1.5">₹{{ number_format($netExpectedPayout, 2) }}</p>
            <span class="text-[10px] text-gray-400 block mt-3.5">(Earnings - Advances)</span>
        </div>
    </div>

    {{-- Quick Actions Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">⚡ Quick Links & Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('employee.clients') }}" class="flex items-center gap-4 p-4 border border-gray-100 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-900/30 rounded-xl transition-all">
                <span class="text-2xl">💼</span>
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white text-sm">Assigned Work</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Assigned clients & channels</p>
                </div>
            </a>
            <a href="{{ route('employee.salaries') }}" class="flex items-center gap-4 p-4 border border-gray-100 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-900/30 rounded-xl transition-all">
                <span class="text-2xl">💰</span>
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white text-sm">Salaries Payout</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Monthly pay overview & receipts</p>
                </div>
            </a>
            <a href="{{ route('employee.advances') }}" class="flex items-center gap-4 p-4 border border-gray-100 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-900/30 rounded-xl transition-all">
                <span class="text-2xl">💸</span>
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white text-sm">Advance Requests</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Apply for advances & ledgers</p>
                </div>
            </a>
            <a href="{{ route('employee.holidays') }}" class="flex items-center gap-4 p-4 border border-gray-100 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-900/30 rounded-xl transition-all">
                <span class="text-2xl">🌴</span>
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white text-sm">Holiday Requests</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Apply for leave & check statuses</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
