@extends('layouts.app')
@section('title', 'Employee Holiday Requests')
@section('page-title', 'Employee Holiday Requests')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Employee Holiday Requests</h2>
            <p class="text-sm text-gray-500">Review, approve, or reject employee leave and holiday requests.</p>
        </div>
        <a href="{{ route('salary.index') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-250 text-xs font-semibold rounded-xl transition-all hover:bg-gray-250">
            ← Salaries Home
        </a>
    </div>

    {{-- Pending Holiday Requests Section --}}
    <div id="holiday-requests-container" class="bg-white dark:bg-gray-800 rounded-2xl p-6 mb-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            <span>🌴 Pending Holiday Requests</span>
            <span id="holiday-req-badge" class="bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-400 px-2 py-0.5 rounded-full text-xs font-bold">{{ $pendingHolidayRequests->count() }}</span>
        </h3>
        
        <div class="space-y-4">
            @forelse($pendingHolidayRequests as $hReq)
                <div id="holiday-req-card-{{ $hReq->id }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 bg-gray-50/50 dark:bg-gray-900/20 border border-gray-100 dark:border-gray-700/60 rounded-xl gap-4">
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">
                            {{ $hReq->employee->name }} requested leave from <span class="text-purple-650 font-extrabold">{{ $hReq->start_date->format('d/m/Y') }}</span> to <span class="text-purple-650 font-extrabold">{{ $hReq->end_date->format('d/m/Y') }}</span>
                        </p>
                        @if($hReq->reason)
                            <p class="text-xs text-gray-500 mt-1">Reason: "{{ $hReq->reason }}"</p>
                        @endif
                        <p class="text-[10px] text-gray-400 mt-0.5">Requested on {{ $hReq->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="button" onclick="submitAjaxApproval('/salary/holiday-requests/{{ $hReq->id }}/approve', 'holiday-req-card-{{ $hReq->id }}', 'holiday-req-badge', 'holiday-requests-container')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all">
                            Approve Leave
                        </button>
                        <button type="button" onclick="openRejectHolidayModal({{ $hReq->id }}, '{{ $hReq->employee->name }}')" class="px-4 py-2 bg-red-655 hover:bg-red-750 text-white text-xs font-semibold rounded-lg shadow-sm transition-all">
                            Reject
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">No pending holiday requests found.</p>
            @endforelse
        </div>
    </div>

    {{-- Processed History Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-750 bg-gray-50/50 dark:bg-gray-700/10">
            <h3 class="font-bold text-gray-800 dark:text-white">📜 Holiday Request Log (Recent History)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Employee</th>
                        <th class="px-6 py-3.5">Leave Duration</th>
                        <th class="px-6 py-3.5">Reason</th>
                        <th class="px-6 py-3.5">Action Status</th>
                        <th class="px-6 py-3.5">Admin Comments</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-750">
                    @forelse($processedHolidayRequests as $hReq)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors text-sm">
                            <td class="px-6 py-3.5 whitespace-nowrap font-bold text-gray-800 dark:text-white">
                                {{ $hReq->employee->name }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                {{ $hReq->start_date->format('d/m/Y') }} — {{ $hReq->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3.5 text-gray-500">
                                {{ $hReq->reason }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($hReq->status === 'approved') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($hReq->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-500">
                                @if($hReq->status === 'rejected')
                                    <span class="text-xs text-red-705 font-medium">Reason: "{{ $hReq->rejection_reason ?? 'None provided' }}"</span>
                                @else
                                    <span class="text-xs text-gray-400">Approved by Admin</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">No processed requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Reject Holiday Modal --}}
<div id="reject-holiday-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-150 dark:border-gray-700 relative">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Reject Holiday Leave</h3>
        <p class="text-sm text-gray-500 mb-4 font-semibold">Please provide rejection reason for leave requested by <span id="reject-holiday-emp-name" class="text-purple-650"></span>.</p>
        <form id="reject-holiday-form" onsubmit="submitAjaxHolidayRejection(event, '/salary/holiday-requests/', 'holiday-req-badge', 'holiday-requests-container')">
            @csrf
            <input type="hidden" id="reject-holiday-id">
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Reason for Rejection *</label>
                <textarea id="reject-holiday-reason" required placeholder="Specify reason (e.g. scheduling conflicts, critical project milestone)..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm focus:ring-2 focus:ring-red-500" rows="3"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectHolidayModal()" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-red-655 hover:bg-red-700 text-white font-medium rounded-xl shadow-lg text-sm">Submit Rejection</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openRejectHolidayModal(id, empName) {
        document.getElementById('reject-holiday-id').value = id;
        document.getElementById('reject-holiday-emp-name').innerText = empName;
        document.getElementById('reject-holiday-reason').value = '';
        document.getElementById('reject-holiday-modal').classList.remove('hidden');
    }

    function closeRejectHolidayModal() {
        document.getElementById('reject-holiday-modal').classList.add('hidden');
    }

    function submitAjaxApproval(url, cardId, badgeId, containerId) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(cardId);
                if (card) {
                    card.style.transition = 'all 0.5s ease';
                    card.style.transform = 'scale(0.9)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Update badge
                        const badge = document.getElementById(badgeId);
                        if (badge) {
                            let count = parseInt(badge.innerText) - 1;
                            badge.innerText = count;
                            if (count <= 0) {
                                document.getElementById(containerId).remove();
                            }
                        }
                    }, 500);
                }
            } else {
                alert(data.message || 'Error occurred');
            }
        })
        .catch(err => console.error(err));
    }

    function submitAjaxHolidayRejection(event, baseUrl, badgeId, containerId) {
        event.preventDefault();
        const id = document.getElementById('reject-holiday-id').value;
        const reason = document.getElementById('reject-holiday-reason').value;
        const url = `${baseUrl}${id}/reject`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeRejectHolidayModal();
                const card = document.getElementById(`holiday-req-card-${id}`);
                if (card) {
                    card.style.transition = 'all 0.5s ease';
                    card.style.transform = 'scale(0.9)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Update badge
                        const badge = document.getElementById(badgeId);
                        if (badge) {
                            let count = parseInt(badge.innerText) - 1;
                            badge.innerText = count;
                            if (count <= 0) {
                                document.getElementById(containerId).remove();
                            }
                        }
                    }, 500);
                }
            } else {
                alert(data.message || 'Error occurred');
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endpush
@endsection
