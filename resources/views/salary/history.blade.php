@extends('layouts.app')
@section('title', 'Salary History — Listing ERP')
@section('page-title', 'Salary History')

@section('content')
<div class="fade-in">
    {{-- Header & Sub-Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Salary History</h2>
            <p class="text-sm text-gray-500">Comprehensive historical archive of all generated & paid salaries</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('salary.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-xl transition-all">
                ← Back to Current Month
            </a>
            <a href="{{ route('salary.advance.form') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm">
                💸 Advance Request
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 space-x-4">
        <a href="{{ route('salary.index') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            📅 Monthly Generator
        </a>
        <a href="{{ route('salary.history') }}" class="py-3 px-4 text-sm font-bold text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400">
            📜 Full History Archive
        </a>
        <a href="{{ route('admin.advances') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            💵 Advance Requests
        </a>
        <a href="{{ route('admin.holidays') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            🏖️ Holiday Requests
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-semibold">Total Paid (All Time)</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">₹{{ number_format($totalPaidSum, 2) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Cleared salary disbursements</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-semibold">Total Commission Paid</p>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1">₹{{ number_format($totalCommissionSum, 2) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Performance commissions</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-semibold">Total Net Payable</p>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">₹{{ number_format($totalNetPayableSum, 2) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Cumulative net generated</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <form method="GET" action="{{ route('salary.history') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-48">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Employee</label>
                <select name="employee_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-32">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Year</label>
                <select name="year" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Years</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-36">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Month</label>
                <select name="month" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="w-32">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="generated" {{ request('status') == 'generated' ? 'selected' : '' }}>Generated (Unpaid)</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>

            <div class="flex items-end gap-2 pt-5">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm">
                    Filter
                </button>
                <a href="{{ route('salary.history') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-semibold rounded-xl transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- History Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-700/50 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Employee</th>
                        <th class="px-5 py-3">Month & Year</th>
                        <th class="px-5 py-3 text-right">Base Salary</th>
                        <th class="px-5 py-3 text-right">Commission</th>
                        <th class="px-5 py-3 text-right">Advance Deduct</th>
                        <th class="px-5 py-3 text-right">Net Payable</th>
                        <th class="px-5 py-3 text-right">Paid Amount</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center">Paid Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($salaries as $sal)
                        <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-700/30 transition-colors text-sm">
                            <td class="px-5 py-4 font-semibold text-gray-800 dark:text-white">
                                <a href="{{ route('employees.show', $sal->employee_id) }}" class="hover:text-blue-600 transition-colors">
                                    {{ $sal->employee->name ?? '—' }}
                                </a>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300 font-medium">
                                {{ $sal->month_name ?? \Carbon\Carbon::create($sal->year, $sal->month, 1)->format('F Y') }}
                            </td>
                            <td class="px-5 py-4 text-right text-gray-700 dark:text-gray-300">
                                ₹{{ number_format($sal->base_salary, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right font-medium text-emerald-600">
                                +₹{{ number_format($sal->total_commission, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right font-medium text-red-500">
                                -₹{{ number_format($sal->advance_deduction, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($sal->net_payable, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right font-bold text-emerald-600">
                                ₹{{ number_format($sal->paid_amount, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sal->status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                    {{ ucfirst($sal->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center text-xs text-gray-500 whitespace-nowrap">
                                {{ $sal->paid_date ? $sal->paid_date->format('d M Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                                No salary history records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($salaries->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $salaries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
