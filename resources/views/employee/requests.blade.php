@extends('layouts.app')
@section('title', 'My Requests Ledger — Listing ERP')
@section('page-title', 'My Requests Ledger')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Requests Ledger & Advances</h2>
            <p class="text-sm text-gray-500">Track and submit your advance money and holiday leave requests.</p>
        </div>
        <div class="flex gap-2.5">
            <button onclick="openModal('advance-request-modal')" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1">
                💸 Request Advance
            </button>
            <button onclick="openModal('holiday-request-modal')" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1">
                🌴 Request Holiday
            </button>
            <a href="{{ route('employee.dashboard') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-250 text-xs font-semibold rounded-xl transition-all hover:bg-gray-250">
                ← Dashboard
            </a>
        </div>
    </div>

    {{-- Category & Sub-Category Tab Navigation --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 space-x-6">
        <button onclick="switchTab('money')" id="tab-btn-money" class="pb-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600 focus:outline-none transition-all flex items-center gap-1.5">
            💸 Money & Advances
        </button>
        <button onclick="switchTab('holiday')" id="tab-btn-holiday" class="pb-3 text-sm font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 border-b-2 border-transparent focus:outline-none transition-all flex items-center gap-1.5">
            🌴 Holiday & Leaves
        </button>
    </div>

    {{-- Subpage 1: Money & Advances --}}
    <div id="tab-content-money" class="tab-pane">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Ledger Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Outstanding Balance & Net Expected Payout --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <p class="text-xs text-gray-400 uppercase font-medium">Outstanding Advance Balance</p>
                        <p class="text-3xl font-extrabold text-red-600 mt-2">₹{{ number_format($pendingAdvanceBalance, 2) }}</p>
                        <p class="text-[10px] text-gray-500 mt-2">This outstanding amount will be automatically deducted in your upcoming salary payouts.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <p class="text-xs text-green-600 dark:text-green-400 uppercase font-bold">Net Payout This Month</p>
                        <p class="text-3xl font-extrabold text-green-600 dark:text-green-400 mt-2">₹{{ number_format($netExpectedPayout, 2) }}</p>
                        <p class="text-[10px] text-gray-500 mt-2">Calculated after applying outstanding advance deductions to your base salary/commissions.</p>
                    </div>
                </div>

                {{-- Advance Payouts Ledger Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-750 flex justify-between items-center bg-gray-50/50 dark:bg-gray-700/10">
                        <h3 class="font-bold text-gray-800 dark:text-white">💰 Received Advance Disbursements</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Total Disbursed</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Paid / Deducted</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Remaining Balance</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-150 dark:divide-gray-750">
                                @forelse($advances as $adv)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors text-sm">
                                        <td class="px-6 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                            {{ $adv->advance_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap font-bold text-gray-850 dark:text-white">
                                            ₹{{ number_format($adv->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-gray-500">
                                            ₹{{ number_format($adv->deducted, 2) }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap font-semibold text-red-650">
                                            ₹{{ number_format($adv->remaining, 2) }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                @if($adv->status === 'settled') bg-green-100 text-green-800
                                                @else bg-red-105 text-red-800 @endif">
                                                {{ ucfirst($adv->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">No active advance payouts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right: Advance Requests --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">⏳ My Advance Requests</h3>
                    <div class="space-y-3.5">
                        @forelse($advanceRequests as $req)
                            <div class="border border-gray-100 dark:border-gray-750 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-900/20">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-extrabold text-gray-850 dark:text-white">₹{{ number_format($req->amount, 2) }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $req->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($req->status === 'approved') bg-green-100 text-green-800
                                        @elseif($req->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </div>
                                @if($req->notes)
                                    <p class="text-xs text-gray-500 mt-2 bg-white dark:bg-gray-850 p-2 rounded-lg border border-gray-100 dark:border-gray-700">{{ $req->notes }}</p>
                                @endif
                                @if($req->status === 'rejected' && $req->rejection_reason)
                                    <div class="mt-2 text-xs text-red-700 font-semibold bg-red-50 dark:bg-red-950/20 p-2 rounded-lg border border-red-105 dark:border-red-900/30">
                                        ❌ Rejection Reason: "{{ $req->rejection_reason }}"
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-450 text-center py-6">No advance requests submitted yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subpage 2: Holiday & Leaves --}}
    <div id="tab-content-holiday" class="tab-pane hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Holiday & Policy Information --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase font-medium">Holiday & Leave Policy</p>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mt-2">Track & Request Leave Time</h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Please submit your holiday leave requests at least 3 days in advance. Once submitted, your supervisor will review and update the status. You will receive notifications upon approval or rejection.
                    </p>
                </div>
            </div>

            {{-- Right: Holiday Requests --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">🌴 My Holiday Requests</h3>
                    <div class="space-y-3.5">
                        @forelse($holidayRequests as $hReq)
                            <div class="border border-gray-100 dark:border-gray-750 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-900/20">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-gray-800 dark:text-white">
                                            {{ $hReq->start_date->format('d/m/Y') }} — {{ $hReq->end_date->format('d/m/Y') }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $hReq->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($hReq->status === 'approved') bg-green-100 text-green-800
                                        @elseif($hReq->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($hReq->status) }}
                                    </span>
                                </div>
                                @if($hReq->reason)
                                    <p class="text-xs text-gray-500 mt-2 bg-white dark:bg-gray-850 p-2 rounded-lg border border-gray-100 dark:border-gray-700">{{ $hReq->reason }}</p>
                                @endif
                                @if($hReq->status === 'rejected' && $hReq->rejection_reason)
                                    <div class="mt-2 text-xs text-red-700 font-semibold bg-red-50 dark:bg-red-950/20 p-2 rounded-lg border border-red-105 dark:border-red-900/30">
                                        ❌ Rejection Reason: "{{ $hReq->rejection_reason }}"
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-450 text-center py-6">No holiday requests submitted yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Request Advance Modal --}}
<div id="advance-request-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-xl relative animate-scale-up">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Request Advance Payment</h3>
        <form method="POST" action="{{ route('employee.advance-request') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Request Amount (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="e.g. 3000" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Reason / Notes (Optional)</label>
                    <textarea name="notes" placeholder="Please specify the reason for advance..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('advance-request-modal')" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg text-sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

{{-- Request Holiday Modal --}}
<div id="holiday-request-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-xl relative animate-scale-up">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Request Holiday Leave</h3>
        <form method="POST" action="{{ route('employee.holiday-request') }}">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Start Date *</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">End Date *</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Reason for Leave *</label>
                    <textarea name="reason" required placeholder="Reason for requesting holiday/leave..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('holiday-request-modal')" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl shadow-lg text-sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function switchTab(tab) {
    if (tab === 'money') {
        document.getElementById('tab-content-money').classList.remove('hidden');
        document.getElementById('tab-content-holiday').classList.add('hidden');
        
        document.getElementById('tab-btn-money').className = "pb-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600 focus:outline-none transition-all flex items-center gap-1.5";
        document.getElementById('tab-btn-holiday').className = "pb-3 text-sm font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 border-b-2 border-transparent focus:outline-none transition-all flex items-center gap-1.5";
    } else {
        document.getElementById('tab-content-money').classList.add('hidden');
        document.getElementById('tab-content-holiday').classList.remove('hidden');
        
        document.getElementById('tab-btn-money').className = "pb-3 text-sm font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 border-b-2 border-transparent focus:outline-none transition-all flex items-center gap-1.5";
        document.getElementById('tab-btn-holiday').className = "pb-3 text-sm font-bold text-purple-600 border-b-2 border-purple-600 focus:outline-none transition-all flex items-center gap-1.5";
    }
}
</script>
@endpush
@endsection
