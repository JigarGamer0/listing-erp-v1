@extends('layouts.app')
@section('title', 'My Holiday Requests — Listing ERP')
@section('page-title', 'My Holiday Requests')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Holiday & Leave Requests</h2>
            <p class="text-sm text-gray-500">Track and submit your holiday leave requests history.</p>
        </div>
        <div class="flex gap-2.5">
            <button onclick="openModal('holiday-request-modal')" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1">
                🌴 Request Holiday Leave
            </button>
            <a href="{{ route('employee.dashboard') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-250 text-xs font-semibold rounded-xl transition-all hover:bg-gray-250">
                ← Dashboard
            </a>
        </div>
    </div>

    {{-- Layout Grid --}}
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
</script>
@endpush
@endsection
