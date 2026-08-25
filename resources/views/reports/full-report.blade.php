@extends('layouts.app')
@section('title', 'Full Financial Report — Listing ERP')
@section('page-title', 'Complete Hisab Report')

@section('content')
<div class="fade-in">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">📊 Complete Financial Report</h2>
            <p class="text-sm text-gray-500">
                @if($filterByDate)
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                @else
                    All-Time Data (No date filter applied)
                @endif
            </p>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none">
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-all">Filter</button>
            <a href="{{ route('reports.full-report') }}" class="px-5 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-all">All Time</a>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- FUND SUMMARY CARD: Top-Level Balance Sheet --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 mb-8 shadow-lg">
        <h3 class="text-lg font-bold text-white/90 mb-4">💰 Fund Balance Sheet (All-Time)</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                <p class="text-xs text-white/70 uppercase font-medium">Total Collection</p>
                <p class="text-xl font-bold text-emerald-300 mt-1">₹{{ number_format($allTimeCollection, 2) }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                <p class="text-xs text-white/70 uppercase font-medium">Total Expenses</p>
                <p class="text-xl font-bold text-red-300 mt-1">-₹{{ number_format($allTimeExpenses, 2) }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                <p class="text-xs text-white/70 uppercase font-medium">Total Advances</p>
                <p class="text-xl font-bold text-orange-300 mt-1">-₹{{ number_format($allTimeAdvances, 2) }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                <p class="text-xs text-white/70 uppercase font-medium">Total Salary Paid</p>
                <p class="text-xl font-bold text-yellow-300 mt-1">-₹{{ number_format($allTimeSalaryPaid, 2) }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border-2 border-white/30">
                <p class="text-xs text-white/90 uppercase font-bold">Available Fund</p>
                <p class="text-2xl font-extrabold {{ $availableFund >= 0 ? 'text-emerald-200' : 'text-red-300' }} mt-1">₹{{ number_format($availableFund, 2) }}</p>
            </div>
        </div>
        <div class="mt-4 bg-white/5 rounded-lg p-3">
            <p class="text-xs text-white/60 font-mono">
                Formula: Available Fund = Total Collection (₹{{ number_format($allTimeCollection, 2) }}) − Expenses (₹{{ number_format($allTimeExpenses, 2) }}) − Advances (₹{{ number_format($allTimeAdvances, 2) }}) − Salary Paid (₹{{ number_format($allTimeSalaryPaid, 2) }}) = <strong class="text-white">₹{{ number_format($availableFund, 2) }}</strong>
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: INCOME — Kiske kitne aye --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600 text-lg">💵</div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Client Payments Received</h3>
                <p class="text-sm text-gray-500">Kisse kitne paise aaye — client wise breakdown</p>
            </div>
            <span class="ml-auto text-2xl font-bold text-emerald-600">₹{{ number_format($totalCollection, 2) }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-emerald-50 dark:bg-emerald-900/20">
                    <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Client</th>
                    <th class="px-5 py-3 text-center font-semibold text-gray-500 uppercase">Payments</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Total Received</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($clientWiseCollection as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-semibold text-gray-800 dark:text-white">{{ $item['client']->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3 text-center text-gray-500">{{ $item['count'] }} payments</td>
                            <td class="px-5 py-3 text-right font-bold text-emerald-600">₹{{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">No payments found.</td></tr>
                    @endforelse
                </tbody>
                @if($clientWiseCollection->isNotEmpty())
                <tfoot>
                    <tr class="bg-emerald-50 dark:bg-emerald-900/20 font-bold">
                        <td class="px-5 py-3 text-gray-800 dark:text-white">TOTAL</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $allPayments->count() }} payments</td>
                        <td class="px-5 py-3 text-right text-emerald-700">₹{{ number_format($totalCollection, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: EXPENSES — Kisko kitne diye --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center text-red-600 text-lg">📤</div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Expenses</h3>
                <p class="text-sm text-gray-500">Category wise kitne kharche hue</p>
            </div>
            <span class="ml-auto text-2xl font-bold text-red-600">-₹{{ number_format($totalExpenses, 2) }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-red-50 dark:bg-red-900/20">
                    <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-5 py-3 text-center font-semibold text-gray-500 uppercase">Entries</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Total Amount</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($categoryWiseExpense as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-semibold text-gray-800 dark:text-white">{{ $item['category'] }}</td>
                            <td class="px-5 py-3 text-center text-gray-500">{{ $item['count'] }}</td>
                            <td class="px-5 py-3 text-right font-bold text-red-600">-₹{{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">No expenses found.</td></tr>
                    @endforelse
                </tbody>
                @if($categoryWiseExpense->isNotEmpty())
                <tfoot>
                    <tr class="bg-red-50 dark:bg-red-900/20 font-bold">
                        <td class="px-5 py-3 text-gray-800 dark:text-white">TOTAL EXPENSES</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $allExpenses->count() }} entries</td>
                        <td class="px-5 py-3 text-right text-red-700">-₹{{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: SALARIES — Employee ko kitne diye --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center text-yellow-600 text-lg">👤</div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Employee Salaries</h3>
                <p class="text-sm text-gray-500">Employee wise kitni salary di aur kitni pending hai</p>
            </div>
            <span class="ml-auto text-2xl font-bold text-yellow-600">-₹{{ number_format($totalSalaryPaid, 2) }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-yellow-50 dark:bg-yellow-900/20">
                    <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Employee</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Paid</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Pending</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($employeeWiseSalary as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-semibold text-gray-800 dark:text-white">{{ $item['employee']->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3 text-right font-bold text-yellow-600">₹{{ number_format($item['total_paid'], 2) }}</td>
                            <td class="px-5 py-3 text-right text-orange-500">₹{{ number_format($item['total_pending'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">No salary records found.</td></tr>
                    @endforelse
                </tbody>
                @if($employeeWiseSalary->isNotEmpty())
                <tfoot>
                    <tr class="bg-yellow-50 dark:bg-yellow-900/20 font-bold">
                        <td class="px-5 py-3 text-gray-800 dark:text-white">TOTAL SALARY</td>
                        <td class="px-5 py-3 text-right text-yellow-700">₹{{ number_format($totalSalaryPaid, 2) }}</td>
                        <td class="px-5 py-3 text-right text-orange-600">₹{{ number_format($employeeWiseSalary->sum('total_pending'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 4: ADVANCES — Employee ko kitna advance diya --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center text-orange-600 text-lg">💸</div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Employee Advances</h3>
                <p class="text-sm text-gray-500">Kitna advance diya, kitna kata aur kitna baki hai</p>
            </div>
            <span class="ml-auto text-2xl font-bold text-orange-600">-₹{{ number_format($totalAdvances, 2) }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-orange-50 dark:bg-orange-900/20">
                    <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Employee</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Given</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Deducted</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Remaining</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($employeeWiseAdvance as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-semibold text-gray-800 dark:text-white">{{ $item['employee']->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3 text-right font-bold text-orange-600">₹{{ number_format($item['total_given'], 2) }}</td>
                            <td class="px-5 py-3 text-right text-green-600">₹{{ number_format($item['total_deducted'], 2) }}</td>
                            <td class="px-5 py-3 text-right text-red-500 font-bold">₹{{ number_format($item['total_remaining'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No advance records found.</td></tr>
                    @endforelse
                </tbody>
                @if($employeeWiseAdvance->isNotEmpty())
                <tfoot>
                    <tr class="bg-orange-50 dark:bg-orange-900/20 font-bold">
                        <td class="px-5 py-3 text-gray-800 dark:text-white">TOTAL ADVANCE</td>
                        <td class="px-5 py-3 text-right text-orange-700">₹{{ number_format($totalAdvances, 2) }}</td>
                        <td class="px-5 py-3 text-right text-green-700">₹{{ number_format($totalAdvancesDeducted, 2) }}</td>
                        <td class="px-5 py-3 text-right text-red-700">₹{{ number_format($totalAdvancesRemaining, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 5: INVESTOR HISAB --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600 text-lg">🏦</div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Investor Summary</h3>
                <p class="text-sm text-gray-500">Kisne kitna invest kiya, kitna clear hua aur kitna baki hai</p>
            </div>
            <span class="ml-auto text-2xl font-bold text-indigo-600">₹{{ number_format($totalInvested, 2) }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-indigo-50 dark:bg-indigo-900/20">
                    <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Investor</th>
                    <th class="px-5 py-3 text-center font-semibold text-gray-500 uppercase">Entries</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Total Invested</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Cleared</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Uncleared</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($investorSummary as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-semibold text-gray-800 dark:text-white">{{ $item['investor']->name }}</td>
                            <td class="px-5 py-3 text-center text-gray-500">{{ $item['entries_count'] }}</td>
                            <td class="px-5 py-3 text-right font-bold text-indigo-600">₹{{ number_format($item['total_invested'], 2) }}</td>
                            <td class="px-5 py-3 text-right text-green-600">₹{{ number_format($item['total_cleared'], 2) }}</td>
                            <td class="px-5 py-3 text-right text-red-500 font-bold">₹{{ number_format($item['total_uncleared'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No investor records found.</td></tr>
                    @endforelse
                </tbody>
                @if($investorSummary->isNotEmpty())
                <tfoot>
                    <tr class="bg-indigo-50 dark:bg-indigo-900/20 font-bold">
                        <td class="px-5 py-3 text-gray-800 dark:text-white">TOTAL</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $investorSummary->sum('entries_count') }}</td>
                        <td class="px-5 py-3 text-right text-indigo-700">₹{{ number_format($totalInvested, 2) }}</td>
                        <td class="px-5 py-3 text-right text-green-700">₹{{ number_format($totalInvestmentCleared, 2) }}</td>
                        <td class="px-5 py-3 text-right text-red-700">₹{{ number_format($totalInvestmentUncleared, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 6: CLIENT OUTSTANDING DUES --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-pink-100 dark:bg-pink-900/30 rounded-xl flex items-center justify-center text-pink-600 text-lg">⏳</div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Client Outstanding Dues</h3>
                <p class="text-sm text-gray-500">Kisse kitna paisa abhi aana baki hai</p>
            </div>
            <span class="ml-auto text-2xl font-bold text-pink-600">₹{{ number_format($totalOutstanding, 2) }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-pink-50 dark:bg-pink-900/20">
                    <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Client</th>
                    <th class="px-5 py-3 text-center font-semibold text-gray-500 uppercase">Pending Cycles</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Outstanding</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($clientDues as $client)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-semibold text-gray-800 dark:text-white">
                                <a href="{{ route('clients.show', $client) }}" class="hover:text-blue-600 transition-colors">{{ $client->name }}</a>
                            </td>
                            <td class="px-5 py-3 text-center text-gray-500">{{ $client->billingCycles->count() }} cycles</td>
                            <td class="px-5 py-3 text-right font-bold text-pink-600">₹{{ number_format($client->total_pending_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">🎉 No outstanding dues! Sab clear hai!</td></tr>
                    @endforelse
                </tbody>
                @if($clientDues->isNotEmpty())
                <tfoot>
                    <tr class="bg-pink-50 dark:bg-pink-900/20 font-bold">
                        <td class="px-5 py-3 text-gray-800 dark:text-white">TOTAL OUTSTANDING</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $clientDues->sum(fn($c) => $c->billingCycles->count()) }} cycles</td>
                        <td class="px-5 py-3 text-right text-pink-700">₹{{ number_format($totalOutstanding, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- FINAL SUMMARY BOX --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-gray-200 dark:border-gray-600 p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">📝 Final Summary</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">💰 Total Collection (Clients se aaya)</span>
                <span class="text-sm font-bold text-emerald-600">+ ₹{{ number_format($filterByDate ? $totalCollection : $allTimeCollection, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">📤 Total Expenses (Kharche)</span>
                <span class="text-sm font-bold text-red-600">- ₹{{ number_format($filterByDate ? $totalExpenses : $allTimeExpenses, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">👤 Total Salary Paid (Employee salary)</span>
                <span class="text-sm font-bold text-yellow-600">- ₹{{ number_format($filterByDate ? $totalSalaryPaid : $allTimeSalaryPaid, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">💸 Total Advances Given</span>
                <span class="text-sm font-bold text-orange-600">- ₹{{ number_format($filterByDate ? $totalAdvances : $allTimeAdvances, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">🏦 Investor Money (Uncleared balance)</span>
                <span class="text-sm font-bold text-indigo-600">₹{{ number_format($totalInvestmentUncleared, 2) }} pending</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">⏳ Client Outstanding (Abhi aana baki hai)</span>
                <span class="text-sm font-bold text-pink-600">₹{{ number_format($totalOutstanding, 2) }} receivable</span>
            </div>
            <div class="flex justify-between items-center py-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl px-4 mt-2">
                <span class="text-base font-bold text-gray-800 dark:text-white">🏦 Available Fund Balance</span>
                <span class="text-xl font-extrabold {{ $availableFund >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₹{{ number_format($availableFund, 2) }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
