@extends('layouts.app')
@section('title', $client->name)
@section('page-title', 'Client Profile')

@section('content')
@php
    $lastCycle = $client->billingCycles->sortByDesc('billing_end')->first();
    $nextStart = $lastCycle ? $lastCycle->billing_end->addDay() : ($client->service_start_date ? $client->service_start_date->addMonth() : now());
    $nextEnd = $nextStart->copy()->addMonth()->subDay();
@endphp

<div class="fade-in" x-data="{ 
    activeTab: 'overview',
    showRenewModal: false,
    showDeleteClientModal: false,
    showPaymentModal: false,
    paymentAmount: {{ (float)$client->total_due > 0 ? (float)$client->total_due : (float)$client->current_package }},
    paymentDate: '{{ date('Y-m-d') }}',
    paymentMethod: 'cash',
    paymentNotes: '',
    renewPackageOption: 'same',
    renewPackageAmount: {{ (float)$client->current_package }},
    renewStartDate: '{{ $nextStart->format('Y-m-d') }}',
    renewEndDate: '{{ $nextEnd->format('Y-m-d') }}',
    renewCollectPayment: false,
    renewPaymentAmount: {{ (float)$client->current_package }},
    renewPaymentMethod: 'cash',
    renewPaymentNotes: ''
}">
    {{-- Profile Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold">{{ strtoupper(substr($client->name, 0, 2)) }}</div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $client->name }}</h2>
                    <div class="flex items-center space-x-3 mt-1 text-sm text-gray-500">
                        <span>📱 {{ $client->mobile }}{{ $client->mobile_secondary ? ' / ' . $client->mobile_secondary : '' }}</span>
                        <span>✉️ {{ $client->email ?? '—' }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $client->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($client->status) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="showRenewModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                    🔄 Renew Client
                </button>
                <button type="button" @click="showPaymentModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-all">
                    💰 Receive Payment
                </button>
                <a href="{{ route('clients.edit', $client) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl transition-all">✏️ Edit</a>
                @if($client->status === 'inactive')
                    <button type="button" @click="showDeleteClientModal = true" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-all flex items-center gap-1.5">
                        🗑️ Delete Client
                    </button>
                @endif
            </div>
        </div>

        {{-- Key Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Package</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white">₹{{ number_format($client->current_package, 0) }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Outstanding</p>
                <p class="text-lg font-bold text-red-600">₹{{ number_format($outstandingBalance, 0) }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Advance</p>
                <p class="text-lg font-bold text-emerald-600">₹{{ number_format($advanceBalance, 0) }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Start Date</p>
                <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $client->service_start_date->format('d/m/Y') }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Location</p>
                <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ str_replace('_', ' ', ucfirst($client->work_location)) }}</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex space-x-1 px-4 py-2">
                @foreach(['overview' => 'Overview', 'payments' => 'Payment Ledger', 'packages' => 'Package History', 'gst' => 'GST History', 'accounts' => 'Accounts', 'documents' => 'Documents', 'notes' => 'Notes', 'timeline' => 'Timeline'] as $key => $label)
                    <button @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all whitespace-nowrap">{{ $label }}</button>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            {{-- Overview Tab --}}
            <div x-show="activeTab === 'overview'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-800 dark:text-white">Client Details</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700"><span class="text-gray-500">Manager</span><span class="font-medium text-gray-800 dark:text-white">{{ $client->manager?->name ?? '—' }}</span></div>
                            <div class="py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500 block mb-1">Assigned Employees</span>
                                <div class="space-y-2">
                                    @forelse($client->employeeAssignments->where('status', 'active') as $assignment)
                                        <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700/30 p-2 rounded-xl">
                                            <span class="font-medium text-gray-800 dark:text-white text-sm">
                                                {{ $assignment->employee->name }}
                                                @if($assignment->gst_count > 0)
                                                    <span class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 px-2 py-0.5 rounded-full ml-1 border border-blue-100 dark:border-blue-900/50">
                                                        GST: {{ $assignment->gst_count }} {{ $assignment->gst_platform ? '(' . ucfirst($assignment->gst_platform) . ')' : '' }}
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-2 py-0.5 rounded-full border border-amber-100 dark:border-amber-900/50">
                                                @if($assignment->commission_type === 'percentage')
                                                    Custom: {{ $assignment->commission_value }}% (Base: ₹{{ number_format($assignment->custom_package_amount ?? $client->current_package, 0) }})
                                                @else
                                                    Custom: ₹{{ number_format($assignment->commission_value, 0) }}
                                                @endif
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">—</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700"><span class="text-gray-500">Flipkart GST</span><span class="font-medium text-gray-800 dark:text-white">{{ $client->current_flipkart_gst }} Accounts</span></div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700"><span class="text-gray-500">Meesho GST</span><span class="font-medium text-gray-800 dark:text-white">{{ $client->current_meesho_gst }} Accounts</span></div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700"><span class="text-gray-500">Joining Date</span><span class="font-medium text-gray-800 dark:text-white">{{ $client->joining_date->format('d/m/Y') }}</span></div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="space-y-3" x-data="{ showPackage: false, showGst: false, showManager: false, showStatus: false, showFollowUp: false }">
                        <h3 class="font-semibold text-gray-800 dark:text-white">Quick Actions</h3>
                        <div class="space-y-2">
                            <button @click="showPackage = !showPackage" class="w-full text-left px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-xl hover:bg-blue-100 transition-all text-sm font-medium">📦 Change Package</button>
                            <div x-show="showPackage" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <form method="POST" action="{{ route('clients.change-package', $client) }}">@csrf
                                    <input type="number" name="new_package" step="0.01" min="0" placeholder="New package amount" required class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <input type="text" name="reason" placeholder="Reason (optional)" class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Update Package</button>
                                </form>
                            </div>

                            <button @click="showGst = !showGst" class="w-full text-left px-4 py-2.5 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 rounded-xl hover:bg-purple-100 transition-all text-sm font-medium">📊 Change GST</button>
                            <div x-show="showGst" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <form method="POST" action="{{ route('clients.change-gst', $client) }}">@csrf
                                    <select name="gst_type" class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="flipkart">Flipkart GST</option>
                                        <option value="meesho">Meesho GST</option>
                                    </select>
                                    <input type="number" name="new_amount" min="0" placeholder="New GST count" required class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <input type="text" name="reason" placeholder="Reason" class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm">Update GST</button>
                                </form>
                            </div>

                            <button @click="showStatus = !showStatus" class="w-full text-left px-4 py-2.5 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-xl hover:bg-amber-100 transition-all text-sm font-medium">🔄 Change Status</button>
                            <div x-show="showStatus" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <form method="POST" action="{{ route('clients.change-status', $client) }}">@csrf
                                    <select name="status" class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="active" {{ $client->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $client->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="archived" {{ $client->status == 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm">Update Status</button>
                                </form>
                            </div>

                            <button @click="showFollowUp = !showFollowUp" class="w-full text-left px-4 py-2.5 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 rounded-xl hover:bg-teal-100 transition-all text-sm font-medium">📅 Schedule Follow-up</button>
                            <div x-show="showFollowUp" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <form method="POST" action="{{ route('follow-ups.store') }}">@csrf
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <input type="date" name="follow_up_date" required class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <input type="text" name="note" placeholder="Note" class="w-full px-3 py-2 border rounded-lg mb-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Schedule</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Ledger Tab --}}
            <div x-show="activeTab === 'payments'">
                <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Billing Cycles & Payment Ledger</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Period</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Due</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Paid</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Balance</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($client->billingCycles as $cycle)
                                <tr>
                                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $cycle->billing_start->format('d/m/Y') }} — {{ $cycle->billing_end->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-800 dark:text-white">₹{{ number_format($cycle->total_due, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-600 font-medium">₹{{ number_format($cycle->total_paid, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-medium {{ $cycle->balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">₹{{ number_format(abs($cycle->balance), 2) }}</td>
                                    <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $cycle->status === 'paid' ? 'bg-green-100 text-green-800' : ($cycle->status === 'overdue' ? 'bg-red-100 text-red-800' : ($cycle->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">{{ ucfirst($cycle->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h4 class="font-semibold text-gray-800 dark:text-white mt-6 mb-3">Payment History</h4>
                <div class="space-y-2">
                    @foreach($client->payments as $payment)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">₹{{ number_format($payment->amount, 2) }} — {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->payment_date->format('d/m/Y') }} by {{ $payment->receivedByUser?->name }}</p>
                                @if($payment->notes)<p class="text-xs text-gray-400 mt-1">{{ $payment->notes }}</p>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Package History Tab --}}
            <div x-show="activeTab === 'packages'">
                <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Package Change History</h3>
                <div class="space-y-3">
                    @forelse($client->packageHistory as $history)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm"><span class="text-red-600 line-through">₹{{ number_format($history->old_package, 2) }}</span> → <span class="text-emerald-600 font-semibold">₹{{ number_format($history->new_package, 2) }}</span></p>
                                <p class="text-xs text-gray-500 mt-1">{{ $history->change_date->format('d/m/Y') }} by {{ $history->changedByUser?->name }}</p>
                                @if($history->reason)<p class="text-xs text-gray-400">Reason: {{ $history->reason }}</p>@endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No package changes recorded</p>
                    @endforelse
                </div>
            </div>

            {{-- GST History Tab --}}
            <div x-show="activeTab === 'gst'">
                <h3 class="font-semibold text-gray-800 dark:text-white mb-4">GST Change History</h3>
                <div class="space-y-3">
                    @forelse($client->gstHistory as $history)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-medium">{{ ucfirst($history->gst_type) }}</span>
                                <p class="text-sm mt-1"><span class="text-red-600 line-through">{{ $history->old_amount }} Accounts</span> → <span class="text-emerald-600 font-semibold">{{ $history->new_amount }} Accounts</span></p>
                                <p class="text-xs text-gray-500 mt-1">{{ $history->change_date->format('d/m/Y') }} by {{ $history->changedByUser?->name }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No GST changes recorded</p>
                    @endforelse
                </div>
            </div>

            {{-- Accounts Tab --}}
            <div x-show="activeTab === 'accounts'" x-data="{ showAdd: false }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Platform Accounts</h3>
                    <button @click="showAdd = !showAdd" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition-all">+ Add Account</button>
                </div>
                <div x-show="showAdd" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl mb-4">
                    <form method="POST" action="{{ route('client-accounts.store', $client) }}">@csrf
                        <div class="grid grid-cols-2 gap-3">
                            <select name="platform" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"><option value="Meesho">Meesho</option><option value="Flipkart">Flipkart</option><option value="Amazon">Amazon</option><option value="Other">Other</option></select>
                            <input type="text" name="store_name" placeholder="Store Name" required class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <input type="text" name="login_id" placeholder="Login ID" required class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <input type="text" name="login_password" placeholder="Password" required class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <textarea name="notes" placeholder="Notes" class="w-full mt-3 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="1"></textarea>
                        <button type="submit" class="mt-3 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">Save Account</button>
                    </form>
                </div>
                <div class="space-y-3">
                    @forelse($client->accounts as $account)
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl" x-data="{ showPassword: false }">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-xs font-medium">{{ $account->platform }}</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white mt-1">{{ $account->store_name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Login: {{ $account->login_id }}</p>
                                    <p class="text-xs text-gray-500">Password: <span x-show="!showPassword">••••••••</span><span x-show="showPassword">{{ $account->login_password }}</span>
                                        <button @click="showPassword = !showPassword" class="text-blue-600 ml-1" x-text="showPassword ? 'Hide' : 'Show'"></button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No accounts added</p>
                    @endforelse
                </div>
            </div>

            {{-- Documents Tab --}}
            <div x-show="activeTab === 'documents'" x-data="{ showUpload: false }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Documents</h3>
                    <button @click="showUpload = !showUpload" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition-all">+ Upload</button>
                </div>
                <div x-show="showUpload" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl mb-4">
                    <form method="POST" action="{{ route('client-documents.store', $client) }}" enctype="multipart/form-data">@csrf
                        <input type="text" name="title" placeholder="Document Title" required class="w-full px-3 py-2 border rounded-lg text-sm mb-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <input type="file" name="document" required class="w-full px-3 py-2 border rounded-lg text-sm mb-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">Upload</button>
                    </form>
                </div>
                <div class="space-y-2">
                    @forelse($client->documents as $doc)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $doc->title }}</p>
                                <p class="text-xs text-gray-500">{{ $doc->file_name }} · {{ number_format($doc->file_size / 1024, 1) }} KB · {{ $doc->created_at->format('d/m/Y') }}</p>
                            </div>
                            <a href="{{ route('client-documents.download', $doc) }}" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-200">Download</a>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No documents uploaded</p>
                    @endforelse
                </div>
            </div>

            {{-- Notes Tab --}}
            <div x-show="activeTab === 'notes'">
                <div class="mb-4">
                    <form method="POST" action="{{ route('clients.add-note', $client) }}" class="flex gap-3">@csrf
                        <input type="text" name="note" placeholder="Add a note..." required class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-all">Add Note</button>
                    </form>
                </div>
                <div class="space-y-2">
                    @forelse($client->notes as $note)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-sm text-gray-800 dark:text-white">{{ $note->note }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $note->createdByUser?->name }} · {{ $note->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No notes yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Timeline Tab --}}
            <div x-show="activeTab === 'timeline'">
                <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Activity Timeline</h3>
                <div class="relative pl-6 border-l-2 border-blue-200 dark:border-blue-800 space-y-4">
                    @forelse($client->timeline as $event)
                        <div class="relative">
                            <div class="absolute -left-[1.65rem] w-3 h-3 bg-blue-600 rounded-full border-2 border-white dark:border-gray-800"></div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <p class="text-sm text-gray-800 dark:text-white">{{ $event->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $event->createdByUser?->name ?? 'System' }} · {{ $event->created_at->format('d/m/Y h:i A') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No timeline events</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Renew Client Modal --}}
    <div x-show="showRenewModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" 
         x-transition 
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full border border-gray-100 dark:border-gray-700 shadow-2xl p-6 relative overflow-hidden" 
             @click.away="showRenewModal = false">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold">🔄</span>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Renew Client Package</h3>
                </div>
                <button type="button" @click="showRenewModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <div class="mb-4 p-3.5 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100/50 dark:border-indigo-900/30 rounded-xl">
                <p class="text-xs text-gray-500 dark:text-gray-400">Client Details</p>
                <div class="flex justify-between items-center mt-0.5">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $client->name }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold">Current: ₹{{ number_format($client->current_package, 0) }}</span>
                </div>
            </div>

            <form action="{{ route('clients.renew', $client) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    {{-- Package Choice Option --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Package Option *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="renewPackageOption === 'same' ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-300 font-bold' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                                <input type="radio" name="package_option" value="same" x-model="renewPackageOption" class="text-indigo-600 mr-2.5">
                                <div class="text-xs">
                                    <p>Same Package</p>
                                    <p class="text-[10px] text-gray-400 font-normal">₹{{ number_format($client->current_package, 0) }}</p>
                                </div>
                            </label>

                            <label class="flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="renewPackageOption === 'new' ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-300 font-bold' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                                <input type="radio" name="package_option" value="new" x-model="renewPackageOption" class="text-indigo-600 mr-2.5">
                                <div class="text-xs">
                                    <p>New Package</p>
                                    <p class="text-[10px] text-gray-400 font-normal">Change monthly price</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- New Package Amount Input (Visible only if 'new') --}}
                    <div x-show="renewPackageOption === 'new'" x-transition>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">New Package Amount (₹) *</label>
                        <input type="number" step="0.01" min="0" name="new_package_amount" x-model="renewPackageAmount" placeholder="Enter new package amount" class="w-full px-4 py-2.5 border border-indigo-300 dark:border-indigo-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Billing Cycle Dates --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Billing Start Date *</label>
                            <input type="date" name="billing_start" x-model="renewStartDate" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Billing End Date *</label>
                            <input type="date" name="billing_end" x-model="renewEndDate" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Collect Payment Checkbox --}}
                    <div class="pt-2 border-t border-gray-150 dark:border-gray-700">
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="collect_payment" value="1" x-model="renewCollectPayment" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-gray-800 dark:text-white">💰 Receive payment now during renewal</span>
                        </label>

                        <div x-show="renewCollectPayment" x-transition class="mt-3 p-4 bg-gray-50 dark:bg-gray-750/50 rounded-xl space-y-3 border border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-300 mb-1">Amount (₹)</label>
                                    <input type="number" step="0.01" min="1" name="payment_amount" :value="renewPackageOption === 'new' ? renewPackageAmount : {{ (float)$client->current_package }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-300 mb-1">Payment Method</label>
                                    <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="upi">UPI</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-300 mb-1">Payment Notes (Optional)</label>
                                <input type="text" name="notes" placeholder="e.g. Received via GPay" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showRenewModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-xs transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-600/25 transition-all">Confirm Renewal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Inactive Client Modal --}}
    @if($client->status === 'inactive')
        <div x-show="showDeleteClientModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
             x-transition 
             style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full border border-gray-100 dark:border-gray-700 shadow-2xl p-6 relative overflow-hidden"
                 @click.away="showDeleteClientModal = false">
                <div class="flex items-center space-x-3 text-red-600 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center font-bold text-xl">⚠️</div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Delete Inactive Client</h3>
                </div>
                
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                    Are you sure you want to permanently delete inactive client <strong class="text-gray-900 dark:text-white">{{ $client->name }}</strong>?
                </p>
                <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-xl text-xs text-red-800 dark:text-red-300 mb-6">
                    This will delete the client profile and associated records. Only inactive clients can be deleted.
                </div>

                <form action="{{ route('clients.destroy', $client) }}" method="POST" class="flex justify-end space-x-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteClientModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                        Yes, Delete Client
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- In-Page Receive Payment Modal --}}
    <div x-show="showPaymentModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-2xl relative"
             @click.away="showPaymentModal = false">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center font-bold text-sm">💰</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Receive Payment</h3>
                </div>
                <button type="button" @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('payments.store', $client) }}">
                @csrf
                <div class="space-y-4">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-100 dark:border-emerald-900/40">
                        <p class="text-xs text-gray-500">Client: <span class="font-bold text-gray-900 dark:text-white">{{ $client->name }}</span></p>
                        <p class="text-xs text-gray-500 mt-0.5">Total Outstanding: <span class="font-bold text-red-600">₹{{ number_format($client->total_due, 2) }}</span></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" x-model="paymentDate" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Amount (₹) *</label>
                        <input type="number" step="0.01" min="1" name="amount" x-model="paymentAmount" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Payment Method *</label>
                        <select name="payment_method" x-model="paymentMethod" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                        <textarea name="notes" x-model="paymentNotes" rows="2" placeholder="Optional notes" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showPaymentModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-xs">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/25 transition-all text-xs">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
