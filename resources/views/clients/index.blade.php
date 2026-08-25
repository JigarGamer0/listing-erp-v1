@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')
@php
    $totalClients = \App\Models\Client::count();
    $activeClients = \App\Models\Client::where('status', 'active')->count();
    $inactiveClients = \App\Models\Client::where('status', 'inactive')->count();
    
    $activePercent = $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 2) : 0;
    $inactivePercent = $totalClients > 0 ? round(($inactiveClients / $totalClients) * 100, 2) : 0;
    
    // Only ACTIVE clients' due amounts are counted in the dashboard metric
    $paymentDueClientsCount = \App\Models\Client::where('status', 'active')->paymentDue()->count();
    $paymentDue = \App\Models\ClientBillingCycle::whereHas('client', function($q) {
        $q->where('status', 'active');
    })->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance');
    
    $creditClientsCount = 0;
    $availableCredit = 0;
    foreach(\App\Models\Client::where('status', 'active')->get() as $c) {
        $bal = $c->advance_balance;
        if ($bal > 0) {
            $creditClientsCount++;
            $availableCredit += $bal;
        }
    }
    $currentStatus = request('status', 'active');
@endphp

<div class="fade-in" x-data="{
    showPaymentModal: false,
    selectedClient: null,
    cycles: [],
    selectedCycleId: '',
    showCycleField: false,
    paymentAmount: 0,
    paymentDate: '{{ date('Y-m-d') }}',
    paymentMethod: 'cash',
    paymentNotes: '',
    
    // Renewal Modal State
    showRenewModal: false,
    renewClient: null,
    renewPackageOption: 'same',
    renewPackageAmount: 0,
    renewStartDate: '',
    renewEndDate: '',
    renewCollectPayment: false,
    renewPaymentAmount: 0,
    renewPaymentMethod: 'cash',
    renewPaymentNotes: '',

    // Delete Client Modal State
    showDeleteClientModal: false,
    deleteClientUrl: '',
    deleteClientName: '',

    // Add Client In-Page Modal State
    showAddClientModal: false,
    addClientSecondaryMobile: false,

    openPaymentModal(client) {
        this.selectedClient = client;
        this.cycles = client.billing_cycles || [];
        this.paymentAmount = client.total_due || 0;
        this.selectedCycleId = '';
        this.showCycleField = false;

        const today = new Date().toISOString().split('T')[0];
        const pendingCycleAfterDueDate = this.cycles.find(bc => {
            return (bc.status === 'pending' || bc.status === 'partial' || bc.status === 'overdue') && today > bc.due_date;
        });

        if (pendingCycleAfterDueDate) {
            this.showCycleField = true;
            this.selectedCycleId = pendingCycleAfterDueDate.id;
        }

        this.showPaymentModal = true;
    },

    openRenewModal(client) {
        this.renewClient = client;
        this.renewPackageOption = 'same';
        this.renewPackageAmount = client.current_package || 0;
        this.renewCollectPayment = false;
        this.renewPaymentAmount = client.current_package || 0;
        this.renewPaymentMethod = 'cash';
        this.renewPaymentNotes = '';

        // Calculate next start date & end date (1 month)
        let startDate = new Date();
        if (client.next_cycle_start) {
            startDate = new Date(client.next_cycle_start);
        }
        
        let endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + 1);
        endDate.setDate(endDate.getDate() - 1);

        this.renewStartDate = startDate.toISOString().split('T')[0];
        this.renewEndDate = endDate.toISOString().split('T')[0];

        this.showRenewModal = true;
    }
}">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Clients</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Dashboard > Clients</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <button type="button" @click="showAddClientModal = true" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm hover:shadow shadow-blue-500/25">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                + Add New Client
            </button>
        </div>
    </div>

    {{-- Tabs (Active vs Inactive vs All) --}}
    <div class="flex border-b border-gray-200 dark:border-gray-800 mb-6 space-x-2">
        <a href="{{ route('clients.index', array_merge(request()->except('page'), ['status' => 'active'])) }}" 
           class="py-3 px-4 text-sm font-semibold flex items-center gap-2 border-b-2 transition-all {{ $currentStatus === 'active' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <span>🟢 Active Clients</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $currentStatus === 'active' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' }}">{{ $activeClients }}</span>
        </a>

        <a href="{{ route('clients.index', array_merge(request()->except('page'), ['status' => 'inactive'])) }}" 
           class="py-3 px-4 text-sm font-semibold flex items-center gap-2 border-b-2 transition-all {{ $currentStatus === 'inactive' ? 'border-amber-600 text-amber-600 dark:text-amber-400 dark:border-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <span>🔴 Inactive Clients</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $currentStatus === 'inactive' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' }}">{{ $inactiveClients }}</span>
        </a>

        <a href="{{ route('clients.index', array_merge(request()->except('page'), ['status' => 'all'])) }}" 
           class="py-3 px-4 text-sm font-semibold flex items-center gap-2 border-b-2 transition-all {{ $currentStatus === 'all' ? 'border-purple-600 text-purple-600 dark:text-purple-400 dark:border-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <span>📋 All Clients</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $currentStatus === 'all' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' }}">{{ $totalClients }}</span>
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 mb-6 border border-gray-100 dark:border-gray-800 shadow-sm">
        <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="status" value="{{ $currentStatus }}">

            {{-- Search input --}}
            <div class="relative flex-1 min-w-[240px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client name or mobile..." 
                       class="w-full pl-9 pr-4 py-2 bg-gray-50/50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-gray-600 dark:text-gray-300">
            </div>

            {{-- Dropdowns --}}
            <div class="w-36">
                <select name="manager_id" class="w-full px-3 py-2 bg-gray-50/50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-600 dark:text-gray-300 outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Managers</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-36">
                <select name="employee_id" class="w-full px-3 py-2 bg-gray-50/50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-600 dark:text-gray-300 outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Employees</option>
                    @foreach(\App\Models\Employee::active()->get() as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm">
                    Filter
                </button>
                <a href="{{ route('clients.index', ['status' => $currentStatus]) }}" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-semibold rounded-xl transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Metrics Summaries --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        {{-- Card 1: Total Clients --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm relative">
            <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold">Total Clients</p>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalClients }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">All database records</p>
        </div>

        {{-- Card 2: Active Clients --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm relative">
            <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold">Active Clients</p>
            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $activeClients }}</h3>
            <p class="text-[10px] text-emerald-500 mt-1">{{ $activePercent }}% active rate</p>
        </div>

        {{-- Card 3: Inactive Clients --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm relative">
            <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold">Inactive Clients</p>
            <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $inactiveClients }}</h3>
            <p class="text-[10px] text-amber-500 mt-1">{{ $inactivePercent }}% inactive</p>
        </div>

        {{-- Card 4: Payment Due (Active Only) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm relative">
            <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold">Active Payment Due</p>
            <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">₹{{ number_format($paymentDue, 0) }}</h3>
            <p class="text-[10px] text-red-500 mt-1">{{ $paymentDueClientsCount }} Active clients (Inactive excluded)</p>
        </div>

        {{-- Card 5: Advance Balance --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm relative">
            <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold">Advance Credit</p>
            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">₹{{ number_format($availableCredit, 0) }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">{{ $creditClientsCount }} Active Clients</p>
        </div>
    </div>

    {{-- Main Clients List Panel --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white">
                {{ $currentStatus === 'active' ? 'Active Clients' : ($currentStatus === 'inactive' ? 'Inactive Clients' : 'All Clients') }}
                <span class="text-xs font-normal text-gray-400">({{ $clients->total() }} found)</span>
            </h3>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800 text-left">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Package</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Manager</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Due Amount</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Next Renewal</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @php
                        $colors = ['bg-blue-600', 'bg-emerald-600', 'bg-purple-600', 'bg-orange-600', 'bg-indigo-600', 'bg-pink-600', 'bg-teal-600'];
                    @endphp
                    @forelse($clients as $idx => $client)
                        @php
                            $words = explode(' ', $client->name);
                            $initials = '';
                            foreach($words as $w) {
                                $initials .= strtoupper(substr($w, 0, 1));
                            }
                            $initials = substr($initials, 0, 2);
                            $bgColor = $colors[$idx % count($colors)];

                            $lastCycle = $client->billingCycles->sortByDesc('billing_end')->first();
                            $nextRenewalDate = $lastCycle ? $lastCycle->billing_end->addDay() : ($client->service_start_date ? $client->service_start_date->addMonth() : null);
                            $isRenewalDue = $nextRenewalDate ? $nextRenewalDate->lte(now()->addDays(7)) : false;
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/10 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full {{ $bgColor }} text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <a href="{{ route('clients.show', $client) }}" class="text-xs font-bold text-gray-900 dark:text-white hover:text-blue-600 transition-colors">{{ $client->name }}</a>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $client->mobile }}{{ $client->mobile_secondary ? ' / ' . $client->mobile_secondary : '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">Standard</p>
                                <p class="text-[10px] text-gray-400">₹{{ number_format($client->current_package, 0) }}/Month</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $client->manager->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $client->assignedEmployee->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                    {{ $client->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($client->status === 'active' && $client->total_due > 0)
                                    <span class="text-xs font-bold text-red-500">₹{{ number_format($client->total_due, 0) }}</span>
                                @elseif($client->status === 'inactive')
                                    <span class="text-xs text-gray-400 italic">Inactive</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs whitespace-nowrap">
                                @if($nextRenewalDate)
                                    <span class="{{ $isRenewalDue ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $nextRenewalDate->format('d M Y') }}
                                    </span>
                                    @if($isRenewalDue)
                                        <span class="ml-1 text-[10px] px-1.5 py-0.2 bg-amber-100 text-amber-800 rounded font-semibold">Due</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center space-x-1.5">
                                    {{-- Renew Client Button --}}
                                    <button type="button" 
                                            @click="openRenewModal({{ json_encode([
                                                'id' => $client->id, 
                                                'name' => $client->name, 
                                                'current_package' => (float)$client->current_package,
                                                'next_cycle_start' => $nextRenewalDate ? $nextRenewalDate->format('Y-m-d') : date('Y-m-d')
                                            ]) }})"
                                            class="inline-flex items-center px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg text-xs font-semibold transition-colors" 
                                            title="Renew Client for Next Month">
                                        🔄 Renew
                                    </button>

                                    {{-- Receive Payment Button --}}
                                    <button @click="openPaymentModal({{ json_encode([
                                        'id' => $client->id, 
                                        'name' => $client->name, 
                                        'mobile' => $client->mobile, 
                                        'total_due' => $client->total_due, 
                                        'billing_cycles' => $client->billingCycles->map(function($bc) { 
                                            return [
                                                'id' => $bc->id, 
                                                'start' => $bc->billing_start->format('d M Y'), 
                                                'end' => $bc->billing_end->format('d M Y'), 
                                                'status' => $bc->status, 
                                                'due_date' => $bc->billing_end->addDays(5)->format('Y-m-d')
                                            ]; 
                                        })
                                    ]) }})" 
                                    class="p-1 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg text-gray-400 hover:text-emerald-600 transition-colors" 
                                    title="Receive Payment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>

                                    {{-- View Profile --}}
                                    <a href="{{ route('clients.show', $client) }}" class="p-1 hover:bg-gray-150 dark:hover:bg-gray-800 rounded-lg text-gray-400 hover:text-blue-600 transition-colors" title="View Profile">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('clients.edit', $client) }}" class="p-1 hover:bg-gray-150 dark:hover:bg-gray-800 rounded-lg text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    {{-- Delete Button (Only for Inactive Clients) --}}
                                    @if($client->status === 'inactive')
                                        <button type="button" 
                                                @click="deleteClientUrl = '{{ route('clients.destroy', $client) }}'; deleteClientName = '{{ addslashes($client->name) }}'; showDeleteClientModal = true;"
                                                class="p-1 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg text-red-500 hover:text-red-700 transition-colors" 
                                                title="Delete Inactive Client">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-450 dark:text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-sm font-medium">No {{ $currentStatus !== 'all' ? $currentStatus : '' }} clients found.</p>
                                <a href="{{ route('clients.create') }}" class="text-blue-600 hover:underline text-xs mt-1 inline-block">Add a client</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        @if($clients->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Inactive Client Modal --}}
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
                Are you sure you want to permanently delete inactive client <strong class="text-gray-900 dark:text-white" x-text="deleteClientName"></strong>?
            </p>
            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-xl text-xs text-red-800 dark:text-red-300 mb-6">
                This will delete the client profile and associated records. Only inactive clients can be deleted.
            </div>

            <form :action="deleteClientUrl" method="POST" class="flex justify-end space-x-3">
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
                <p class="text-xs text-gray-500 dark:text-gray-400">Selected Client</p>
                <div class="flex justify-between items-center mt-0.5">
                    <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="renewClient ? renewClient.name : ''"></p>
                    <span class="text-xs px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold" x-text="'Current: ₹' + (renewClient ? Number(renewClient.current_package).toLocaleString('en-IN') : '0')"></span>
                </div>
            </div>

            <form :action="renewClient ? '/clients/' + renewClient.id + '/renew' : '#'" method="POST">
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
                                    <p class="text-[10px] text-gray-400 font-normal" x-text="'₹' + (renewClient ? Number(renewClient.current_package).toLocaleString('en-IN') : '0')"></p>
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
                                    <input type="number" step="0.01" min="1" name="payment_amount" :value="renewPackageOption === 'new' ? renewPackageAmount : (renewClient ? renewClient.current_package : 0)" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-xs outline-none focus:ring-1 focus:ring-indigo-500">
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

    {{-- Receive Payment Modal --}}
    <div x-show="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" x-transition style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full border border-gray-100 dark:border-gray-700 shadow-2xl p-6 relative overflow-hidden" @click.away="showPaymentModal = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Receive Payment</h3>
                <button type="button" @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-100/50 dark:border-blue-900/30 rounded-xl">
                <p class="text-xs text-gray-500 dark:text-gray-400">Client Details</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="selectedClient ? selectedClient.name : ''"></p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5" x-text="selectedClient ? selectedClient.mobile : ''"></p>
            </div>
            
            <form :action="selectedClient ? '/clients/' + selectedClient.id + '/receive-payment' : '#'" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Payment Date</label>
                        <input type="date" name="payment_date" x-model="paymentDate" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Amount (₹)</label>
                        <input type="number" name="amount" x-model="paymentAmount" step="0.01" min="1" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Payment Method</label>
                        <select name="payment_method" x-model="paymentMethod" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    {{-- Which Month (Billing Cycle) Select field --}}
                    <div x-show="showCycleField">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Which Month (Billing Cycle)</label>
                        <select name="billing_cycle_id" x-model="selectedCycleId" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">— Automatically distribute —</option>
                            <template x-for="cycle in cycles" :key="cycle.id">
                                <option :value="cycle.id" x-text="cycle.start + ' — ' + cycle.end + ' (' + cycle.status + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Notes</label>
                        <textarea name="notes" x-model="paymentNotes" rows="2" placeholder="Optional notes" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showPaymentModal = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-sm transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl text-sm shadow-lg shadow-emerald-600/25 transition-all">Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── IN-PAGE ADD CLIENT MODAL ─────────────────────────────── --}}
    <div x-show="showAddClientModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-3xl w-full mx-4 shadow-2xl relative my-8"
             @click.away="showAddClientModal = false">
            <div class="flex items-center justify-between mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center font-bold text-sm">👤</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Add New Client</h3>
                </div>
                <button type="button" @click="showAddClientModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('clients.store') }}">
                @csrf
                <div class="space-y-4 max-h-[72vh] overflow-y-auto px-1">
                    {{-- Row 1: Name & Mobile --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Client / Business Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Ramesh Traders" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Mobile Number *</label>
                                <button type="button" @click="addClientSecondaryMobile = !addClientSecondaryMobile" class="text-[11px] text-blue-600 dark:text-blue-400 hover:underline">
                                    <span x-text="addClientSecondaryMobile ? '- Remove 2nd' : '+ Add 2nd'"></span>
                                </button>
                            </div>
                            <input type="text" name="mobile" required placeholder="10-digit mobile" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs focus:ring-2 focus:ring-blue-500">
                            
                            <div class="mt-2" x-show="addClientSecondaryMobile">
                                <input type="text" name="mobile_secondary" placeholder="Secondary mobile (optional)" class="w-full px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Email & Work Location --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email (Optional)</label>
                            <input type="email" name="email" placeholder="client@example.com" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Work Location *</label>
                            <select name="work_location" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                                <option value="our_office">Our Office</option>
                                <option value="client_office">Client Office</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row 3: Joining & Service Start Date --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Joining Date *</label>
                            <input type="date" name="joining_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Service Start Date *</label>
                            <input type="date" name="service_start_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                        </div>
                    </div>

                    {{-- Row 4: Package & GST --}}
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-750/30 rounded-xl border border-gray-100 dark:border-gray-700">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2.5">Package & GST</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Monthly Package (₹) *</label>
                                <input type="number" step="0.01" min="0" name="current_package" required placeholder="e.g. 5000" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Flipkart GST Count</label>
                                <input type="number" min="0" name="current_flipkart_gst" value="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Meesho GST Count</label>
                                <input type="number" min="0" name="current_meesho_gst" value="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                            </div>
                        </div>
                    </div>

                    {{-- Row 5: Manager & Assigned Staff --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Manager</label>
                            <select name="manager_id" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                                <option value="">— Select Manager —</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Assign Primary Staff (Optional)</label>
                            <select name="assigned_employee_id" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                                <option value="">— Select Employee —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->role_title ?? 'Staff' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Address / Remarks (Optional)</label>
                        <textarea name="address" rows="2" placeholder="Client address..." class="w-full px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="showAddClientModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-xs">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/25 transition-all text-xs">Create Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
