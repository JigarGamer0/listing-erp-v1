@extends('layouts.app')
@section('title', 'Request Salary Advance')
@section('page-title', 'Request Salary Advance')

@section('content')
<div class="max-w-xl mx-auto fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Advance Salary Details</h2>

        <form method="POST" action="{{ route('salary.advance') }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee *</label>
                    <select name="employee_id" id="employee_id" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" data-salary="{{ $emp->total_salary_estimate }}">{{ $emp->name }} (Salary: ₹{{ number_format($emp->total_salary_estimate, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Advance Date *</label>
                    <input type="date" name="advance_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Advance Amount (₹) *</label>
                    <input type="number" name="amount" id="advance_amount" required step="0.01" min="1" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-lg font-semibold" placeholder="₹ Enter amount">
                </div>

                {{-- Dynamic Pending Calculation Section --}}
                <div id="salary-calc-box" class="p-4 bg-gray-50 dark:bg-gray-700/30 border border-gray-150 dark:border-gray-700 rounded-xl space-y-2 hidden">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>Employee Base Salary:</span>
                        <span class="font-semibold text-gray-800 dark:text-white" id="base-salary-val">₹0.00</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>Advance to Deduct:</span>
                        <span class="font-semibold text-red-600" id="advance-deduct-val">-₹0.00</span>
                    </div>
                    <hr class="border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between text-sm font-bold text-gray-800 dark:text-white">
                        <span>Remaining Pending Salary:</span>
                        <span class="text-emerald-600" id="remaining-salary-val">₹0.00</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes / Reason</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none" placeholder="Reason for advance..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('salary.index') }}" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl shadow-lg shadow-purple-600/20 transition-all">💸 Process Advance</button>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const empSelect = document.getElementById('employee_id');
            const amtInput = document.getElementById('advance_amount');
            const calcBox = document.getElementById('salary-calc-box');
            const baseSalaryVal = document.getElementById('base-salary-val');
            const advanceDeductVal = document.getElementById('advance-deduct-val');
            const remainingSalaryVal = document.getElementById('remaining-salary-val');

            function updateRemaining() {
                const selectedOpt = empSelect.options[empSelect.selectedIndex];
                const baseSalary = parseFloat(selectedOpt?.dataset?.salary) || 0;
                const advAmount = parseFloat(amtInput.value) || 0;

                if (!selectedOpt || !selectedOpt.value) {
                    calcBox.classList.add('hidden');
                    return;
                }

                calcBox.classList.remove('hidden');
                baseSalaryVal.textContent = '₹' + baseSalary.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                advanceDeductVal.textContent = '-₹' + advAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 });

                const remaining = Math.max(0, baseSalary - advAmount);
                remainingSalaryVal.textContent = '₹' + remaining.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            }

            empSelect.addEventListener('change', updateRemaining);
            amtInput.addEventListener('input', updateRemaining);
        });
        </script>
    </div>
</div>
@endsection
