@extends('layouts.app')
@section('title', 'My Assigned Clients — Listing ERP')
@section('page-title', 'My Assigned Clients')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Assigned Clients</h2>
            <p class="text-sm text-gray-500">List of clients assigned to you and performance commissions.</p>
        </div>
        <a href="{{ route('employee.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-250 text-xs font-semibold rounded-xl transition-all hover:bg-gray-250">
            ← Back to Dashboard
        </a>
    </div>

    @php
        $totalAssigned = count($clientDetails);
        $completedToday = 0;
        foreach($clientDetails as $c) {
            if(isset($todayLogs[$c['client_id']]) && $todayLogs[$c['client_id']]->is_done) {
                $completedToday++;
            }
        }
        $progressPercent = $totalAssigned > 0 ? round(($completedToday / $totalAssigned) * 100) : 0;
    @endphp

    {{-- Daily Work Progress Bar --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-6">
        <div class="flex justify-between items-center mb-2.5">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">My Daily Work Progress</span>
            <span class="text-sm font-extrabold text-indigo-600 dark:text-indigo-400">{{ $progressPercent }}% Done</span>
        </div>
        <div class="w-full bg-gray-100 dark:bg-gray-700 h-3 rounded-full overflow-hidden mb-1.5">
            <div class="bg-indigo-655 h-full rounded-full transition-all duration-550" style="width: {{ $progressPercent }}%"></div>
        </div>
        <p class="text-[11px] text-gray-400">
            {{ $completedToday }} out of {{ $totalAssigned }} assigned client daily tasks completed today.
        </p>
    </div>

    {{-- Clients Grid/Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Client Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Work Location</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">GST Counts / Platform</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Commission Basis</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Today's Work</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">My Estimated Earnings</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-750">
                    @forelse($clientDetails as $c)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-800 dark:text-white">{{ $c['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($c['work_location'] === 'our_office')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">🏢 Our Office</span>
                                @elseif($c['work_location'] === 'client_office')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300">💼 Client Office</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">🏡 Hybrid</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    @if($c['platform'] === 'flipkart')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-950/20 px-2 py-0.5 rounded w-max">
                                            🛒 Flipkart ({{ $c['gst_count'] }} GSTs)
                                        </span>
                                    @elseif($c['platform'] === 'meesho')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-pink-600 bg-pink-50 dark:bg-pink-950/20 px-2 py-0.5 rounded w-max">
                                            🛒 Meesho ({{ $c['gst_count'] }} GSTs)
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">Other ({{ $c['gst_count'] }} GSTs)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">
                                @if($c['commission_type'] === 'percentage')
                                    {{ $c['commission_value'] }}% of ₹{{ number_format($c['package_amount'], 2) }}/Month
                                @else
                                    Fixed ₹{{ number_format($c['commission_value'], 2) }}/Month
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(isset($todayLogs[$c['client_id']]))
                                    <div class="flex items-center gap-2">
                                        @if($todayLogs[$c['client_id']]->is_done)
                                            <span class="px-2.5 py-1 rounded bg-green-100 text-green-800 text-xs font-semibold flex items-center gap-1">
                                                ✓ Done ({{ $todayLogs[$c['client_id']]->listings_count }} Listings)
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded bg-red-100 text-red-800 text-xs font-semibold">
                                                ❌ Pending
                                            </span>
                                        @endif
                                        <button type="button" onclick="openWorkLogModal({{ $c['client_id'] }}, '{{ $c['name'] }}', {{ $todayLogs[$c['client_id']]->listings_count }}, {{ $todayLogs[$c['client_id']]->is_done ? 'true' : 'false' }}, '{{ $todayLogs[$c['client_id']]->notes ?? '' }}')" class="text-xs text-blue-600 font-bold hover:underline">
                                            Edit
                                        </button>
                                    </div>
                                @else
                                    <button type="button" onclick="openWorkLogModal({{ $c['client_id'] }}, '{{ $c['name'] }}', 0, true, '')" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-750 text-white text-xs font-bold rounded-lg shadow-sm transition-all">
                                        Update Daily Work
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-extrabold text-green-600 dark:text-green-400">
                                ₹{{ number_format($c['commission_amount'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No active client assignments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Daily Work Log Modal --}}
<div id="daily-work-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-xl relative animate-scale-up">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Update Daily Work Log</h3>
        <p class="text-xs text-gray-500 mb-4">Client: <span id="modal-client-name" class="font-extrabold text-indigo-600"></span></p>
        <form method="POST" action="{{ route('employee.daily-work') }}">
            @csrf
            <input type="hidden" name="client_id" id="modal-client-id">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Listings Done Today *</label>
                    <input type="number" min="0" name="listings_count" id="modal-listings-count" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_done" value="1" id="modal-is-done" class="w-4 h-4 text-blue-600 border-gray-350 rounded focus:ring-blue-500">
                    <label for="modal-is-done" class="ml-2 text-xs font-semibold text-gray-700 dark:text-gray-300">Mark work as fully completed for today</label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Notes / Remarks (Optional)</label>
                    <textarea name="notes" id="modal-notes" placeholder="Specify any comments, issues or status updates..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeWorkLogModal()" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-750 text-white font-medium rounded-xl text-sm shadow-md">Save Daily Log</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openWorkLogModal(clientId, clientName, listingsCount, isDone, notes) {
    document.getElementById('modal-client-id').value = clientId;
    document.getElementById('modal-client-name').textContent = clientName;
    document.getElementById('modal-listings-count').value = listingsCount;
    document.getElementById('modal-is-done').checked = isDone;
    document.getElementById('modal-notes').value = notes;
    document.getElementById('daily-work-modal').classList.remove('hidden');
}

function closeWorkLogModal() {
    document.getElementById('daily-work-modal').classList.add('hidden');
}
</script>
@endpush
@endsection
