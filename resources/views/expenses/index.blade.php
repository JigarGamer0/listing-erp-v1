@extends('layouts.app')
@section('title', 'Expenses & Outflows')
@section('page-title', 'Expenses & Outflows')

@section('content')
<div class="fade-in" x-data="{ activeTab: '{{ $activeTab }}', showAddExpenseModal: false }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Company Expenses & Outflows</h2>
            <p class="text-sm text-gray-500 mt-1">
                Track all company expenses, paid salaries, and employee advances in one unified hub.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Month / All Time Selector Form --}}
            <form method="GET" action="{{ route('expenses.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" :value="activeTab">
                <select name="time_frame" onchange="this.form.submit()" class="px-3.5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold text-gray-800 dark:text-white outline-none shadow-sm">
                    <option value="month" {{ $timeFrame === 'month' ? 'selected' : '' }}>Monthly View</option>
                    <option value="all" {{ $timeFrame === 'all' ? 'selected' : '' }}>All-Time Total</option>
                </select>

                @if($timeFrame === 'month')
                    <select name="month" onchange="this.form.submit()" class="px-3.5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold text-gray-800 dark:text-white outline-none shadow-sm">
                        @foreach($months as $m)
                            <option value="{{ $m['month'] }}" {{ $m['month'] == $month && $m['year'] == $year ? 'selected' : '' }}>
                                {{ $m['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="year" value="{{ $year }}">
                @endif
            </form>

            <button type="button" @click="showAddExpenseModal = true" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-600/25 transition-all text-xs flex items-center gap-1.5">
                <span>+ Add Expense</span>
            </button>
        </div>
    </div>

    {{-- ─── 4 Top Financial Stat Cards ─────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Card 1: Total Combined Outflow --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Combined Outflow</p>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">₹{{ number_format($totalCombinedExpenses, 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center text-gray-600 dark:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500 font-medium">
                <span>General Expenses + Salaries + Advances</span>
            </div>
        </div>

        {{-- Card 2: Company Expenses --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-rose-500 uppercase tracking-wider">Company Expenses</p>
                    <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">₹{{ number_format($totalGeneralExpenses, 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-rose-50 dark:bg-rose-900/30 rounded-xl flex items-center justify-center text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500 font-medium">
                <span>{{ $expenses->total() }} logged expense items</span>
            </div>
        </div>

        {{-- Card 3: Paid Salaries --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Paid Salaries</p>
                    <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">₹{{ number_format($totalPaidSalaries, 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500 font-medium">
                <span>Cleared staff payroll</span>
            </div>
        </div>

        {{-- Card 4: Disbursed Advances --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-amber-500 uppercase tracking-wider">Disbursed Advances</p>
                    <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">₹{{ number_format($totalAdvances, 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-xl flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500 font-medium">
                <span>Given advance payouts</span>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation & Filter --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700 flex flex-wrap justify-between items-center px-4 py-2 gap-3">
            <nav class="flex space-x-2">
                <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium'" class="px-4 py-2 text-xs rounded-xl transition-all">
                    All Outflows (₹{{ number_format($totalCombinedExpenses, 0) }})
                </button>
                <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium'" class="px-4 py-2 text-xs rounded-xl transition-all">
                    Company Expenses (₹{{ number_format($totalGeneralExpenses, 0) }})
                </button>
                <button @click="activeTab = 'salaries'" :class="activeTab === 'salaries' ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium'" class="px-4 py-2 text-xs rounded-xl transition-all">
                    Paid Salaries (₹{{ number_format($totalPaidSalaries, 0) }})
                </button>
                <button @click="activeTab = 'advances'" :class="activeTab === 'advances' ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium'" class="px-4 py-2 text-xs rounded-xl transition-all">
                    Advances (₹{{ number_format($totalAdvances, 0) }})
                </button>
            </nav>

            <a href="{{ route('salary.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                <span>Manage Monthly Payroll →</span>
            </a>
        </div>

        {{-- Filter for General Expenses (Shown when on General or All tab) --}}
        <div x-show="activeTab === 'general' || activeTab === 'all'" class="p-4 bg-gray-50/50 dark:bg-gray-750/30 border-b border-gray-100 dark:border-gray-700">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="time_frame" value="{{ $timeFrame }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="tab" :value="activeTab">

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Search Expenses</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, remarks..." class="w-full px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                </div>
                <div class="w-44">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Category</label>
                    <select name="category_id" class="w-full px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Type</label>
                    <select name="type" class="w-full px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none">
                        <option value="">All Types</option>
                        <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="one_time" {{ request('type') == 'one_time' ? 'selected' : '' }}>One Time</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-900 dark:bg-gray-600 text-white text-xs font-bold rounded-xl shadow-sm">
                    Filter
                </button>
            </form>
        </div>

        {{-- ─── TAB 1: ALL COMBINED OUTFLOWS / GENERAL EXPENSES ─── --}}
        <div x-show="activeTab === 'all' || activeTab === 'general'">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/70 dark:bg-gray-700/40 text-left text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <th class="px-5 py-3.5">Expense Title</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Type</th>
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-5 py-3.5 text-center">Deduction Status</th>
                            <th class="px-5 py-3.5 text-right">Amount</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($expenses as $exp)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-5 py-4">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $exp->title }}</span>
                                    @if($exp->notes)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $exp->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                    <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ $exp->category?->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500 font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $exp->type)) }}
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500 font-medium">
                                    {{ $exp->expense_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $exp->include_in_calculation ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                        {{ $exp->include_in_calculation ? '✓ Included' : '⏸️ Unticked' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right text-sm font-extrabold text-rose-600">
                                    ₹{{ number_format($exp->amount, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-xs">
                                    <a href="{{ route('expenses.edit', $exp) }}" class="text-blue-600 hover:underline font-semibold">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    No company expenses logged for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">{{ $expenses->links() }}</div>
            @endif
        </div>

        {{-- ─── TAB 2: PAID SALARIES ─── --}}
        <div x-show="activeTab === 'salaries'">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/70 dark:bg-gray-700/40 text-left text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <th class="px-5 py-3.5">Employee</th>
                            <th class="px-5 py-3.5">Salary Month</th>
                            <th class="px-5 py-3.5 text-right">Base Salary</th>
                            <th class="px-5 py-3.5 text-right">Commission</th>
                            <th class="px-5 py-3.5 text-right">Advance Cut</th>
                            <th class="px-5 py-3.5 text-right">Salary Cuts</th>
                            <th class="px-5 py-3.5 text-right">Paid Amount</th>
                            <th class="px-5 py-3.5 text-center">Paid Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($paidSalaries as $sal)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-5 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ $sal->employee?->name ?? 'Staff #' . $sal->employee_id }}
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                    {{ \Carbon\Carbon::create($sal->year, $sal->month, 1)->format('F Y') }}
                                </td>
                                <td class="px-5 py-4 text-right text-xs text-gray-600">
                                    ₹{{ number_format($sal->base_salary, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-xs text-emerald-600 font-semibold">
                                    +₹{{ number_format($sal->total_commission, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-xs text-red-500 font-semibold">
                                    -₹{{ number_format($sal->advance_deduction, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-xs text-rose-600 font-semibold">
                                    -₹{{ number_format($sal->other_deductions, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-sm font-extrabold text-indigo-600 dark:text-indigo-400">
                                    ₹{{ number_format($sal->paid_amount, 2) }}
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-gray-500">
                                    {{ $sal->paid_date ? $sal->paid_date->format('d M Y') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                                    No paid salaries recorded for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paidSalaries->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">{{ $paidSalaries->links() }}</div>
            @endif
        </div>

        {{-- ─── TAB 3: EMPLOYEE ADVANCES ─── --}}
        <div x-show="activeTab === 'advances'">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/70 dark:bg-gray-700/40 text-left text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <th class="px-5 py-3.5">Employee</th>
                            <th class="px-5 py-3.5">Advance Date</th>
                            <th class="px-5 py-3.5 text-right">Advance Amount</th>
                            <th class="px-5 py-3.5 text-right">Already Deducted</th>
                            <th class="px-5 py-3.5 text-right">Remaining Pending</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($advances as $adv)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-5 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ $adv->employee?->name ?? 'Staff #' . $adv->employee_id }}
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                    {{ $adv->advance_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4 text-right text-sm font-extrabold text-amber-600">
                                    ₹{{ number_format($adv->amount, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-xs text-emerald-600 font-semibold">
                                    ₹{{ number_format($adv->deducted, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-xs text-red-500 font-bold">
                                    ₹{{ number_format($adv->remaining, 2) }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $adv->status === 'fully_deducted' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $adv->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                    No employee advances disbursed in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($advances->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">{{ $advances->links() }}</div>
            @endif
        </div>
    </div>

    {{-- In-Page Add Expense Modal --}}
    <div x-show="showAddExpenseModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-lg w-full mx-4 shadow-2xl relative"
             @click.away="showAddExpenseModal = false">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 flex items-center justify-center font-bold text-sm">💳</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Add New Expense</h3>
                </div>
                <button type="button" @click="showAddExpenseModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Expense Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Office electricity / Internet bill" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                            <select name="category_id" required class="w-full px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                            <select name="type" required class="w-full px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none">
                                <option value="one_time">One Time</option>
                                <option value="monthly">Monthly Recurring</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Amount (₹) *</label>
                            <input type="number" name="amount" required step="0.01" min="0.01" placeholder="₹ Amount" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Expense Date *</label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Receipt Attachment (Optional)</label>
                        <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-xs outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-rose-50 file:text-rose-700">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes / Remarks (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showAddExpenseModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-600/25 transition-all text-sm">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
