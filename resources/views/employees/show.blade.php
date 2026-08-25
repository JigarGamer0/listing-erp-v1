@extends('layouts.app')
@section('title', $employee->name)
@section('page-title', 'Employee Profile')

@section('content')
<div class="fade-in" x-data="{ activeTab: 'overview', showAssignModal: false, showDeleteModal: false, showDeductModal: false }" @open-assign-modal.window="showAssignModal = true">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $employee->name }}</h2>
                    <div class="flex items-center space-x-3 mt-1 text-sm text-gray-500">
                        <span>📱 {{ $employee->phone ?? '—' }}</span>
                        <span>Role: {{ $employee->role_title ?? 'Employee' }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($employee->status) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="showDeductModal = true" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm flex items-center gap-1">
                    <span>✂️ Cut Salary</span>
                </button>
                <a href="{{ route('employees.edit', $employee) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl transition-all">✏️ Edit</a>
                <button type="button" @click="showDeleteModal = true" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-all">🗑️ Delete</button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500">Total Clients</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white">{{ $employee->total_clients }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500">Fixed Salary</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white">₹{{ number_format($employee->fixed_salary, 0) }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500">Pending Comm.</p>
                <p class="text-lg font-bold text-amber-600">₹{{ number_format($employee->total_pending_commission, 0) }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500">Advance</p>
                <p class="text-lg font-bold text-red-600">₹{{ number_format($employee->total_pending_advance, 0) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex space-x-1 px-4 py-2">
                <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-500 hover:bg-gray-100'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Overview</button>
                <button @click="activeTab = 'salaries'" :class="activeTab === 'salaries' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-500 hover:bg-gray-100'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Salaries</button>
                <button @click="activeTab = 'advances'" :class="activeTab === 'advances' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-500 hover:bg-gray-100'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Advances</button>
                <button @click="activeTab = 'deductions'" :class="activeTab === 'deductions' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-500 hover:bg-gray-100'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Salary Cuts / Deductions</button>
            </nav>
        </div>

        <div class="p-6">
            {{-- Overview --}}
            <div x-show="activeTab === 'overview'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white">Assigned Clients</h3>
                            <a href="#" @click.prevent="$dispatch('open-assign-modal')" class="text-sm text-blue-600 hover:underline">+ Assign Client</a>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($clientCommissions as $item)
                                <div class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $item['client']->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            Package: ₹{{ number_format($item['client']->current_package, 0) }}
                                            · Start: {{ $item['client']->service_start_date ? $item['client']->service_start_date->format('d/m/Y') : '—' }}
                                            · Assigned: {{ $item['assignment']->assigned_date ? $item['assignment']->assigned_date->format('d/m/Y') : '—' }}
                                            @if($item['assignment']->gst_count > 0)
                                                 · GST: {{ $item['assignment']->gst_count }} {{ $item['assignment']->gst_platform ? '(' . ucfirst($item['assignment']->gst_platform) . ')' : '' }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white">₹{{ number_format($item['commission_amount'], 0) }}</span>
                                        <form method="POST" action="{{ route('employees.unassign-client', [$employee, $item['assignment']]) }}">
                                            @csrf
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-400 text-sm py-4">No clients assigned.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Employee Details</h3>
                        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex justify-between border-b pb-2"><span>Joining Date</span><span class="font-medium text-gray-800 dark:text-white">{{ $employee->joining_date->format('d/m/Y') }}</span></div>
                            <div class="flex justify-between border-b pb-2"><span>Salary Type</span><span class="font-medium text-gray-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $employee->salary_type)) }}</span></div>
                            <div class="flex justify-between border-b pb-2"><span>Commission Type</span><span class="font-medium text-gray-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $employee->commission_type)) }}</span></div>
                            <div class="flex justify-between border-b pb-2"><span>Commission Value</span><span class="font-medium text-gray-800 dark:text-white">{{ $employee->commission_type === 'percentage' ? $employee->commission_value . '%' : '₹' . number_format($employee->commission_value, 0) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Salaries --}}
            <div x-show="activeTab === 'salaries'">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Salary History</h3>
                    <a href="{{ route('salary.history', ['employee_id' => $employee->id]) }}" class="text-xs text-blue-600 hover:underline font-medium">View Full History Archive →</a>
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="px-4 py-2.5">Month & Year</th>
                                <th class="px-4 py-2.5 text-right">Base Salary</th>
                                <th class="px-4 py-2.5 text-right">Commission</th>
                                <th class="px-4 py-2.5 text-right">Deduction</th>
                                <th class="px-4 py-2.5 text-right">Net Payable</th>
                                <th class="px-4 py-2.5 text-right">Paid Amount</th>
                                <th class="px-4 py-2.5 text-center">Status</th>
                                <th class="px-4 py-2.5 text-center">Paid Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            @forelse($employee->salaries as $salary)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">{{ $salary->month_name ?? \Carbon\Carbon::create($salary->year, $salary->month, 1)->format('F Y') }}</td>
                                    <td class="px-4 py-3 text-right">₹{{ number_format($salary->base_salary, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-600 font-medium">+₹{{ number_format($salary->total_commission, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-red-500 font-medium">-₹{{ number_format($salary->advance_deduction, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">₹{{ number_format($salary->net_payable, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600">₹{{ number_format($salary->paid_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block text-xs font-medium px-2.5 py-0.5 rounded-full {{ $salary->status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                            {{ ucfirst($salary->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $salary->paid_date ? $salary->paid_date->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">No salary history records found for this employee.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Advances --}}
            <div x-show="activeTab === 'advances'">
                <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Salary Advances</h3>
                <div class="space-y-3">
                    @forelse($employee->advances as $adv)
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white">₹{{ number_format($adv->amount, 0) }}</p>
                                <p class="text-xs text-gray-500">Date: {{ $adv->advance_date->format('d/m/Y') }} · Remaining: ₹{{ number_format($adv->remaining, 0) }}</p>
                            </div>
                            <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full {{ $adv->status === 'fully_deducted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst(str_replace('_', ' ', $adv->status)) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No advances found.</p>
                    @endforelse
                </div>
            </div>

            {{-- Salary Deductions / Cuts --}}
            <div x-show="activeTab === 'deductions'">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-white">Salary Deductions & Fines</h3>
                        <p class="text-xs text-gray-400">Monthly cuts applied to salary. Deleting a deduction instantly restores net payable.</p>
                    </div>
                    <button type="button" @click="showDeductModal = true" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center gap-1">
                        <span>+ Deduct Amount</span>
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="px-4 py-2.5">Month & Year</th>
                                <th class="px-4 py-2.5">Amount Cut</th>
                                <th class="px-4 py-2.5">Reason</th>
                                <th class="px-4 py-2.5">Deducted By</th>
                                <th class="px-4 py-2.5">Date</th>
                                <th class="px-4 py-2.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            @forelse($employee->salaryDeductions->sortByDesc('created_at') as $deduction)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                        {{ \Carbon\Carbon::create($deduction->year, $deduction->month, 1)->format('F Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-rose-600">
                                        -₹{{ number_format($deduction->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $deduction->reason }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $deduction->createdByUser?->name ?? 'Admin' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-400">
                                        {{ $deduction->created_at ? $deduction->created_at->format('d/m/Y h:i A') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST" action="{{ route('salary.deductions.destroy', $deduction) }}" onsubmit="return confirm('Cancel this ₹{{ number_format($deduction->amount, 0) }} salary deduction and restore to employee salary?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold hover:underline">
                                                🗑️ Delete & Refund
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        No salary deductions or penalties recorded for this employee.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- Assign Client Modal --}}
    <div x-show="showAssignModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-lg w-full mx-4 shadow-xl relative"
             @click.away="showAssignModal = false">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Assign Client to {{ $employee->name }}</h3>
            
            <form method="POST" action="{{ route('employees.assign-client', $employee) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Client *</label>
                        <select name="client_id" required id="modal_client_id" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                            <option value="">— Select Client —</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" data-package="{{ $client->current_package }}">{{ $client->name }} (Package: ₹{{ number_format($client->current_package, 0) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned GST Count (Optional)</label>
                            <input type="number" name="gst_count" min="0" value="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GST Platform (Optional)</label>
                            <select name="gst_platform" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                                <option value="">— Select Platform —</option>
                                <option value="flipkart">Flipkart</option>
                                <option value="meesho">Meesho</option>
                            </select>
                        </div>
                    </div>

                    <div x-data="{ useCustomCommission: false }">
                        <label class="flex items-center space-x-2 mb-3">
                            <input type="checkbox" x-model="useCustomCommission" class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Override default commission settings</span>
                        </label>

                        <div x-show="useCustomCommission" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/30 border border-gray-150 dark:border-gray-750 rounded-xl space-y-3">
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Commission Type</label>
                                    <select name="custom_commission_type" id="modal_custom_commission_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                                        <option value="fixed_amount">Fixed Amount (₹)</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">INR (Base Amount)</label>
                                    <input type="number" step="0.01" min="0" name="custom_package_amount" id="modal_custom_package_amount" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" placeholder="e.g. 5000">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Value</label>
                                    <input type="number" step="0.01" min="0" name="custom_commission_value" id="modal_custom_commission_value" value="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    {{-- Delete Employee Confirmation Modal --}}
    <div x-show="showDeleteModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-2xl relative"
             @click.away="showDeleteModal = false">
            <div class="flex items-center space-x-3 text-red-600 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center font-bold text-xl">⚠️</div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Delete Employee</h3>
            </div>
            
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Are you sure you want to delete employee <strong class="text-gray-900 dark:text-white">{{ $employee->name }}</strong>?
            </p>
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-xl text-xs text-amber-800 dark:text-amber-300 mb-6">
                All currently active clients assigned to this employee will be safely unassigned. Historical salaries & commissions remain preserved.
            </div>

            <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="flex justify-end space-x-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                    Yes, Delete Employee
                </button>
            </form>
        </div>
    </div>

    {{-- Deduct / Cut Salary Modal --}}
    <div x-show="showDeductModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
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
            
            <form method="POST" action="{{ route('salary.deductions.store', $employee) }}">
                @csrf
                <div class="space-y-4">
                    <div class="p-3.5 bg-rose-50/50 dark:bg-rose-950/30 rounded-xl border border-rose-100/60 dark:border-rose-900/30">
                        <p class="text-xs text-gray-500">Employee: <span class="font-bold text-gray-900 dark:text-white">{{ $employee->name }}</span></p>
                        <p class="text-xs text-gray-500 mt-0.5">Applied to: <span class="font-bold text-gray-800 dark:text-white">{{ date('F Y') }}</span> (Current month only)</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Month *</label>
                            <select name="month" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm font-semibold">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Year *</label>
                            <select name="year" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm font-semibold">
                                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Amount to Cut (₹) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="e.g. 300" 
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-base font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Reason for Deduction *</label>
                        <textarea name="reason" required rows="2" placeholder="e.g. Late reporting / Penalty"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">This will subtract from this month's net payable and send a notification to the employee.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showDeductModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-xl shadow-lg shadow-rose-600/30 transition-all text-sm">Confirm Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
