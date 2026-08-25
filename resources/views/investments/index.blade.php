@extends('layouts.app')
@section('title', 'Investments Tracking')
@section('page-title', 'Investments Tracking')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Investments</h2>
            <p class="text-sm text-gray-500">Track third-party investments and clear them into expenses</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openModal('add-investment-modal')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                ➕ Add Investment
            </button>
            <button onclick="openModal('clear-investment-modal')" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                🧹 Clear Investment
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('investments.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="w-64">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search Investor</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none">
            </div>
            <div class="w-52">
                <label class="block text-xs font-medium text-gray-500 mb-1">Investor</label>
                <select name="investor_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none">
                    <option value="">All Investors</option>
                    @foreach($allInvestors as $inv)
                        <option value="{{ $inv->id }}" {{ request('investor_id') == $inv->id ? 'selected' : '' }}>{{ $inv->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="uncleared" {{ request('status', 'uncleared') === 'uncleared' ? 'selected' : '' }}>Uncleared</option>
                    <option value="cleared" {{ request('status') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all">Filter</button>
            <a href="{{ route('investments.index') }}" class="px-5 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-all">Reset</a>
        </form>
    </div>

    {{-- Investments Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Investor Name</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($investments as $inv)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-5 py-4 font-semibold text-gray-800 dark:text-white">{{ $inv->investor->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-right font-medium text-sm text-gray-900 dark:text-white">₹{{ number_format($inv->amount, 2) }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500">{{ $inv->investment_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $inv->status === 'cleared' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $inv->notes }}">{{ $inv->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">No investments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($investments->hasPages())
            <div class="px-5 py-4 border-t border-gray-150 dark:border-gray-700">
                {{ $investments->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Add Investment Modal --}}
<div id="add-investment-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-xl relative">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Add Investment Entry</h3>
        <form method="POST" action="{{ route('investments.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Select Investor *</label>
                    <select name="investor_id" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                        <option value="">— Select Investor —</option>
                        @foreach($allInvestors as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->name }}{{ $inv->mobile ? ' ('.$inv->mobile.')' : '' }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Investor not listed? <a href="{{ route('investors.index') }}" class="text-blue-600 hover:underline">Create one first →</a></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Amount (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="e.g. 5000" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Investment Date *</label>
                    <input type="date" name="investment_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Notes (Optional)</label>
                    <textarea name="notes" placeholder="Specify what this investment is for (e.g. Ad funds)..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" rows="3"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('add-investment-modal')" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">Save Entry</button>
            </div>
        </form>
    </div>
</div>

{{-- Clear Investment Modal --}}
<div id="clear-investment-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-lg w-full mx-4 shadow-xl relative max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Clear Investor Balances</h3>
        <form method="POST" action="{{ route('investments.clear') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Select Investor *</label>
                    <select name="investor_id" id="clear_investor_select" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                        <option value="">— Select Investor —</option>
                        @foreach($unclearedInvestors as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Uncleared Entries List --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Unclear Entries</label>
                    <div id="uncleared_entries_container" class="max-h-48 overflow-y-auto border border-gray-150 dark:border-gray-750 rounded-xl p-3 space-y-2.5 bg-gray-50 dark:bg-gray-900/30">
                        <p class="text-xs text-gray-400 text-center py-4">Please select an investor to load uncleared entries.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Clear Date *</label>
                    <input type="date" name="clear_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Clearing Notes (Optional)</label>
                    <textarea name="notes" placeholder="Notes for this settlement payout..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" rows="2"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('clear-investment-modal')" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">Clear Selected</button>
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

document.addEventListener('DOMContentLoaded', function() {
    const investorSelect = document.getElementById('clear_investor_select');
    const container = document.getElementById('uncleared_entries_container');

    if (investorSelect) {
        investorSelect.addEventListener('change', function() {
            const investorId = this.value;
            if (!investorId) {
                container.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">Please select an investor to load uncleared entries.</p>';
                return;
            }

            container.innerHTML = '<p class="text-xs text-gray-500 text-center py-4">Loading entries...</p>';

            fetch(`/investments/uncleared?investor_id=${encodeURIComponent(investorId)}`)
                .then(res => res.json())
                .then(entries => {
                    if (entries.length === 0) {
                        container.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">No uncleared entries found.</p>';
                        return;
                    }

                    let html = `
                        <div class="flex items-center space-x-2 pb-2 mb-2 border-b border-gray-200 dark:border-gray-700">
                            <input type="checkbox" id="select_all_investments" class="w-4 h-4 text-indigo-600 rounded">
                            <label for="select_all_investments" class="text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">Select All</label>
                        </div>
                    `;

                    entries.forEach(entry => {
                        const dateStr = new Date(entry.investment_date).toLocaleDateString('en-GB');
                        html += `
                            <label class="flex items-start space-x-3 p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-750 cursor-pointer">
                                <input type="checkbox" name="investment_ids[]" value="${entry.id}" class="investment-checkbox w-4 h-4 text-indigo-600 rounded mt-0.5">
                                <div class="flex-1 text-xs">
                                    <div class="flex justify-between font-bold text-gray-800 dark:text-white">
                                        <span>₹${parseFloat(entry.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                                        <span class="text-gray-400 font-normal">${dateStr}</span>
                                    </div>
                                    <p class="text-gray-500 mt-1">${entry.notes || 'No notes'}</p>
                                </div>
                            </label>
                        `;
                    });

                    container.innerHTML = html;

                    // Bind select all behavior
                    const selectAll = document.getElementById('select_all_investments');
                    const checkboxes = container.querySelectorAll('.investment-checkbox');
                    if (selectAll) {
                        selectAll.addEventListener('change', function() {
                            checkboxes.forEach(cb => cb.checked = this.checked);
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<p class="text-xs text-red-500 text-center py-4">Error loading entries.</p>';
                });
        });
    }
});
</script>
@endpush
@endsection
