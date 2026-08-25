@extends('layouts.app')
@section('title', 'Monthly Salary Management — Listing ERP')
@section('page-title', 'Monthly Salary Management')

@section('content')
<div class="fade-in" x-data="{
    showDeductModal: false,
    deductEmpId: null,
    deductEmpName: '',
    
    showCustomPayoutModal: false,
    payoutEmpId: null,
    payoutEmpName: '',
    payoutBase: 0,
    payoutComm: 0,
    payoutAdv: 0,
    payoutNet: 0,
    payoutMode: 'calculated',
    customAmount: '',
    
    showAdvanceModal: false,
    advEmpId: '',
    advAmount: '',
    advDate: '{{ date('Y-m-d') }}',
    advNotes: '',

    openDeduct(id, name) {
        this.deductEmpId = id;
        this.deductEmpName = name;
        this.showDeductModal = true;
    },

    openCustomPayout(id, name, base, comm, adv, net) {
        this.payoutEmpId = id;
        this.payoutEmpName = name;
        this.payoutBase = base;
        this.payoutComm = comm;
        this.payoutAdv = adv;
        this.payoutNet = net;
        this.payoutMode = 'calculated';
        this.customAmount = '';
        this.showCustomPayoutModal = true;
    }
}">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Monthly Salaries</h2>
            <p class="text-sm text-gray-500">View all active employees, review commissions & 1-click pay pending salaries</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('salary.history') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm">
                📜 Full History Archive
            </a>
            <button type="button" @click="showAdvanceModal = true" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center gap-1">
                <span>💸 Give Advance</span>
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 space-x-4">
        <a href="{{ route('salary.index') }}" class="py-3 px-4 text-sm font-bold text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400">
            📅 Monthly Generator & Payouts
        </a>
        <a href="{{ route('salary.history') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            📜 Full History Archive
        </a>
        <a href="{{ route('admin.advances') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            💵 Advance Requests
        </a>
        <a href="{{ route('admin.holidays') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            🏖️ Holiday Requests
        </a>
    </div>

    {{-- Month & Year Selector --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('salary.index') }}" class="flex items-center gap-3">
            <label class="text-xs font-bold text-gray-600 dark:text-gray-300">Selected Month:</label>
            <select name="month_year" onchange="const parts = this.value.split('-'); window.location.href = '{{ route('salary.index') }}?month=' + parts[0] + '&year=' + parts[1];" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($months as $m)
                    <option value="{{ $m['month'] }}-{{ $m['year'] }}" {{ $month == $m['month'] && $year == $m['year'] ? 'selected' : '' }}>
                        {{ $m['label'] }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('salary.generate') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all">
                    ⚡ Generate All Records
                </button>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        {{-- Total Due --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-amber-500 uppercase font-bold tracking-wider">Pending Salaries To Pay</p>
            <p class="text-3xl font-extrabold text-amber-600 mt-1">₹{{ number_format($totalMonthlyPayrollDue, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Due for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</p>
        </div>

        {{-- Total Paid --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-emerald-500 uppercase font-bold tracking-wider">Salaries Paid This Month</p>
            <p class="text-3xl font-extrabold text-emerald-600 mt-1">₹{{ number_format($totalMonthlyPayrollPaid, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Cleared for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</p>
        </div>

        {{-- Active Employees --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-blue-500 uppercase font-bold tracking-wider">Active Employees</p>
            <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ count($employeeSalaryList) }} Staff</p>
            <p class="text-xs text-gray-400 mt-1">All employees in payroll list</p>
        </div>
    </div>

    {{-- Main Employees Monthly Salary Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 dark:text-white text-base">
                Employee Salary List — {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </h3>
            <span class="text-xs text-gray-500">Click "✓ Done / Pay Salary" to instantly mark as paid</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/70 dark:bg-gray-700/40 text-left text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <th class="px-5 py-3.5">Employee</th>
                        <th class="px-4 py-3.5 text-right">Base Salary</th>
                        <th class="px-4 py-3.5 text-right">Commission</th>
                        <th class="px-4 py-3.5 text-right">Advance Cut</th>
                        <th class="px-4 py-3.5 text-right">Salary Cut (This Month)</th>
                        <th class="px-5 py-3.5 text-right">Net Payable</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Action / One-Click Pay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    @forelse($employeeSalaryList as $row)
                        @php
                            $emp = $row['employee'];
                            $isPaid = $row['status'] === 'paid';
                        @endphp
                        <tr class="hover:bg-gray-50/40 dark:hover:bg-gray-750/30 transition-colors {{ $isPaid ? 'bg-emerald-50/10' : '' }}">
                            {{-- Employee Name & Role --}}
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $emp) }}" class="font-bold text-gray-900 dark:text-white hover:text-blue-600 transition-colors">
                                            {{ $emp->name }}
                                        </a>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ $emp->designation ?: 'Staff' }} · <span class="capitalize">{{ str_replace('_', ' ', $emp->salary_type) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Base Salary --}}
                            <td class="px-4 py-4 text-right text-gray-700 dark:text-gray-300 font-medium">
                                ₹{{ number_format($row['base_salary'], 2) }}
                            </td>

                            {{-- Commission --}}
                            <td class="px-4 py-4 text-right text-emerald-600 font-semibold">
                                +₹{{ number_format($row['commission'], 2) }}
                            </td>

                            {{-- Advance Cut --}}
                            <td class="px-4 py-4 text-right text-red-500 font-semibold">
                                -₹{{ number_format($row['advance_deduction'], 2) }}
                            </td>

                            {{-- Salary Cut / Penalty for This Month --}}
                            <td class="px-4 py-4 text-right">
                                <div class="flex flex-col items-end gap-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold {{ $row['other_deductions'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                                            {{ $row['other_deductions'] > 0 ? '-₹' . number_format($row['other_deductions'], 2) : '₹0.00' }}
                                        </span>
                                        @if(!$isPaid)
                                            <button type="button" 
                                                    @click="openDeduct({{ $emp->id }}, '{{ addslashes($emp->name) }}')"
                                                    class="px-2 py-0.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-300 text-[11px] font-bold rounded-lg transition-all"
                                                    title="Cut/Deduct amount from this month's salary">
                                                ✂️ Cut
                                            </button>
                                        @endif
                                    </div>
                                    @if(count($row['deductions']) > 0)
                                        <div class="space-y-1 mt-0.5 max-w-[180px]">
                                            @foreach($row['deductions'] as $d)
                                                <div class="flex items-center justify-between gap-1 text-[10px] bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 px-2 py-0.5 rounded-md border border-rose-100 dark:border-rose-900/40">
                                                    <span class="truncate" title="{{ $d->reason }}">₹{{ number_format($d->amount, 0) }}: {{ $d->reason }}</span>
                                                    @if(!$isPaid)
                                                        <form method="POST" action="{{ route('salary.deductions.destroy', $d) }}" onsubmit="return confirm('Cancel this ₹{{ number_format($d->amount, 0) }} deduction and refund to salary?');" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold ml-1" title="Delete deduction">&times;</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Net Payable --}}
                            <td class="px-5 py-4 text-right font-extrabold text-base {{ $isPaid ? 'text-gray-700 dark:text-gray-300' : 'text-blue-600 dark:text-blue-400' }}">
                                ₹{{ number_format($row['net_payable'], 2) }}
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($isPaid)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        ✓ Paid
                                    </span>
                                    @if($row['paid_date'])
                                        <p class="text-[10px] text-gray-400 mt-1">{{ \Carbon\Carbon::parse($row['paid_date'])->format('d M Y') }}</p>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        ⏳ Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Action / 1-Click Pay --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if(!$isPaid)
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- 1-Click Done / Pay Button --}}
                                        <form method="POST" action="{{ route('salary.pay-quick', $emp) }}" onsubmit="return confirm('Mark ₹{{ number_format($row['net_payable'], 2) }} salary as PAID for {{ $emp->name }}?');">
                                            @csrf
                                            <input type="hidden" name="month" value="{{ $month }}">
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/25 transition-all flex items-center gap-1">
                                                <span>✓ Done / Pay Salary</span>
                                            </button>
                                        </form>

                                        {{-- Custom / Partial Payout --}}
                                        <button type="button" 
                                                @click="openCustomPayout({{ $emp->id }}, '{{ addslashes($emp->name) }}', {{ $row['base_salary'] }}, {{ $row['commission'] }}, {{ $row['advance_deduction'] }}, {{ $row['net_payable'] }})"
                                                class="px-2.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-semibold rounded-xl transition-all"
                                                title="Custom Amount Payout">
                                            ⚙️
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        Cleared (₹{{ number_format($row['paid_amount'], 2) }})
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                No active employees found in the system.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── MODAL 1: Deduct / Cut Salary Modal (In-Page) ─────────── --}}
    <div x-show="showDeductModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-2xl relative"
             @click.away="showDeductModal = false">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 flex items-center justify-center font-bold text-sm">✂️</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Deduct / Cut Salary</h3>
                </div>
                <button type="button" @click="showDeductModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>
            
            <form :action="'/employees/' + deductEmpId + '/salary-deductions'" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div class="space-y-4">
                    <div class="p-3.5 bg-rose-50/50 dark:bg-rose-950/30 rounded-xl border border-rose-100/60 dark:border-rose-900/30">
                        <p class="text-xs text-gray-500">Employee: <span class="font-bold text-gray-900 dark:text-white" x-text="deductEmpName"></span></p>
                        <p class="text-xs text-gray-500 mt-0.5">Applied to: <span class="font-bold text-gray-800 dark:text-white">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</span> (Only current month)</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Amount to Cut (₹) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="e.g. 300" 
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-base font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Reason for Deduction *</label>
                        <textarea name="reason" required rows="2" placeholder="e.g. Late reporting / Task penalty"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">This deduction will be subtracted from this month's net payable and notified to the employee.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showDeductModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-xl shadow-lg shadow-rose-600/30 transition-all text-sm">Confirm Deduction</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── MODAL 2: Custom Payout Modal (In-Page) ───────────────── --}}
    <div x-show="showCustomPayoutModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-lg w-full mx-4 shadow-2xl relative"
             @click.away="showCustomPayoutModal = false">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Pay Employee Salary</h3>
            
            <form method="POST" action="{{ route('salary.generate-and-pay') }}">
                @csrf
                <input type="hidden" name="employee_id" :value="payoutEmpId">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl space-y-1">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Employee: <span class="font-bold text-gray-900 dark:text-white" x-text="payoutEmpName"></span></p>
                        <p class="text-xs text-gray-500">Period: <span class="font-bold text-gray-800 dark:text-white">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</span></p>
                    </div>

                    <!-- Preview Info -->
                    <div class="space-y-2 text-sm border-t border-b border-gray-100 dark:border-gray-700 py-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Base Salary</span>
                            <span class="font-medium" x-text="'₹' + Number(payoutBase).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Commissions</span>
                            <span class="font-medium text-emerald-600" x-text="'+₹' + Number(payoutComm).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Advance Deduction</span>
                            <span class="font-medium text-red-600" x-text="'-₹' + Number(payoutAdv).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between font-bold border-t border-dashed border-gray-200 dark:border-gray-600 pt-2 text-base">
                            <span class="text-gray-800 dark:text-white">Net Payable</span>
                            <span class="text-blue-600" x-text="'₹' + Number(payoutNet).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>

                    <!-- Payout Mode Options -->
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Payout Option</label>
                        
                        <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750/20">
                            <input type="radio" name="payment_mode" value="calculated" x-model="payoutMode" class="w-4 h-4 text-blue-600">
                            <div>
                                <span class="block text-sm font-medium text-gray-800 dark:text-white">Pay Full Calculated Amount</span>
                                <span class="text-xs text-gray-500">Marks salary as fully paid with calculated Net Payable</span>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750/20">
                            <input type="radio" name="payment_mode" value="custom" x-model="payoutMode" class="w-4 h-4 text-blue-600">
                            <div class="flex-1">
                                <span class="block text-sm font-medium text-gray-800 dark:text-white">Pay Custom Amount</span>
                                <span class="text-xs text-gray-500">Manually enter a custom payout amount</span>
                                <div class="mt-2" x-show="payoutMode === 'custom'">
                                    <input type="number" step="0.01" min="1" name="custom_amount" x-model="customAmount" placeholder="Enter amount (e.g. 5000)" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none">
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showCustomPayoutModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">Confirm Payout</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── MODAL 3: Give Advance Modal (In-Page) ────────────────── --}}
    <div x-show="showAdvanceModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-lg w-full mx-4 shadow-2xl relative"
             @click.away="showAdvanceModal = false">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 flex items-center justify-center font-bold text-sm">💸</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Give Salary Advance</h3>
                </div>
                <button type="button" @click="showAdvanceModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('salary.advance') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Employee *</label>
                        <select name="employee_id" x-model="advEmpId" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                            <option value="">— Select Employee —</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} (Fixed Salary: ₹{{ number_format($emp->fixed_salary, 0) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Advance Date *</label>
                            <input type="date" name="advance_date" x-model="advDate" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Advance Amount (₹) *</label>
                            <input type="number" name="amount" x-model="advAmount" required step="0.01" min="1" placeholder="₹ Amount" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes / Reason (Optional)</label>
                        <textarea name="notes" x-model="advNotes" rows="2" placeholder="Reason for advance..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showAdvanceModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl shadow-lg shadow-purple-600/20 transition-all text-sm">Process Advance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
