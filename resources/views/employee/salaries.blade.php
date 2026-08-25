@extends('layouts.app')
@section('title', 'My Salaries & Payouts — Listing ERP')
@section('page-title', 'My Salaries & Payouts')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Salaries Payouts</h2>
            <p class="text-sm text-gray-500">View generated monthly salary slips and payouts ledger.</p>
        </div>
        <a href="{{ route('employee.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-250 text-xs font-semibold rounded-xl transition-all hover:bg-gray-250">
            ← Dashboard
        </a>
    </div>

    {{-- Salary Details Card --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-3">📋 Salary Structure Overview</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div>
                <span class="text-gray-400 font-medium">Compensation Model:</span>
                <p class="font-bold text-indigo-600 mt-1">
                    @if($employee->salary_type === 'salary')
                        Fixed Salary Base
                    @elseif($employee->salary_type === 'commission')
                        Percentage/Commission Base
                    @else
                        Salary + Commission Base
                    @endif
                </p>
            </div>
            @if(in_array($employee->salary_type, ['salary', 'both']))
                <div>
                    <span class="text-gray-400 font-medium">Fixed Base Salary:</span>
                    <p class="font-bold text-gray-800 dark:text-white mt-1">₹{{ number_format($employee->fixed_salary, 2) }}/Month</p>
                </div>
            @endif
            @if(in_array($employee->salary_type, ['commission', 'both']))
                <div>
                    <span class="text-gray-400 font-medium">Commission Structure:</span>
                    <p class="font-bold text-gray-800 dark:text-white mt-1">
                        @if($employee->commission_type === 'percentage')
                            {{ $employee->commission_value }}% of package amounts
                        @else
                            Fixed ₹{{ number_format($employee->commission_value, 2) }} per client
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Salary History Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-750 bg-gray-50/50 dark:bg-gray-700/10">
            <h3 class="font-bold text-gray-800 dark:text-white">💰 Historical Salary Receipts</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Salary Period</th>
                        <th class="px-6 py-3.5">Base Salary</th>
                        <th class="px-6 py-3.5">Commission Earned</th>
                        <th class="px-6 py-3.5">Advance Deductions</th>
                        <th class="px-6 py-3.5">Actual Paid Amount</th>
                        <th class="px-6 py-3.5 text-right">Payment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-750">
                    @forelse($salaries as $sal)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors text-sm">
                            <td class="px-6 py-3.5 whitespace-nowrap font-bold text-gray-800 dark:text-white">
                                {{ Carbon\Carbon::createFromDate($sal->year, $sal->month, 1)->format('F Y') }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                ₹{{ number_format($sal->base_salary, 2) }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                +₹{{ number_format($sal->commission_amount, 2) }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-red-500 font-semibold">
                                -₹{{ number_format($sal->advance_deduction, 2) }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap font-extrabold text-green-600 dark:text-green-400">
                                ₹{{ number_format($sal->paid_amount, 2) }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    {{ ucfirst($sal->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No salary payments processed yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
