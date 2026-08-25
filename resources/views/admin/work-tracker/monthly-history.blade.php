@extends('layouts.app')
@section('title', 'Monthly Work History — Listing ERP')
@section('page-title', 'Monthly Work History')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Monthly Work History</h2>
            <p class="text-sm text-gray-500">Day-by-day complete work logs and listing counts archive</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('work-tracker.index') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm">
                📅 Go to Daily Tracker
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 space-x-4">
        <a href="{{ route('work-tracker.index') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            📅 Daily Live Tracker
        </a>
        <a href="{{ route('work-tracker.monthly-history') }}" class="py-3 px-4 text-sm font-bold text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400">
            📜 Full Month History
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <form method="GET" action="{{ route('work-tracker.monthly-history') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-40">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Month</label>
                <select name="month" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                    @foreach($months as $m)
                        <option value="{{ $m['num'] }}" {{ $month == $m['num'] ? 'selected' : '' }}>{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-28">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Year</label>
                <select name="year" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            @if(!$isEmployee)
                <div class="w-48">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Employee</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="w-48">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Client</label>
                <select name="client_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Clients</option>
                    @foreach($clients as $cl)
                        <option value="{{ $cl->id }}" {{ $clientId == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 pt-5">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm">
                    View Month
                </button>
                <a href="{{ route('work-tracker.monthly-history') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-semibold rounded-xl transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-semibold">Total Listings In Month</p>
            <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalListingsInMonth) }}</p>
            <p class="text-xs text-gray-500 mt-1">Listings completed across all days</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-semibold">Completed Client Tasks</p>
            <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ number_format($totalCompletedTasks) }}</p>
            <p class="text-xs text-gray-500 mt-1">Tasks marked 'Done'</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-semibold">Active Working Days</p>
            <p class="text-3xl font-extrabold text-purple-600 mt-1">{{ $activeDaysWorked }} Days</p>
            <p class="text-xs text-gray-500 mt-1">Days with logged activities</p>
        </div>
    </div>

    {{-- Day-by-Day Historical Log List --}}
    <div class="space-y-6">
        @php
            $hasAnyLogs = false;
        @endphp

        @foreach($logsByDate as $dateKey => $dayLogs)
            @if($dayLogs->isNotEmpty())
                @php
                    $hasAnyLogs = true;
                    $dayCarbon = \Carbon\Carbon::parse($dateKey);
                    $dayListingsSum = $dayLogs->sum('listings_count');
                    $dayDoneCount = $dayLogs->where('is_done', true)->count();
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                    {{-- Day Header --}}
                    <div class="bg-gray-50/70 dark:bg-gray-700/40 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-extrabold text-sm flex items-center justify-center">
                                {{ $dayCarbon->format('d') }}
                            </span>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-base">
                                    {{ $dayCarbon->format('l, d F Y') }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    {{ $dayDoneCount }} / {{ $dayLogs->count() }} Tasks Done · <span class="font-semibold text-emerald-600">{{ $dayListingsSum }} Listings created</span>
                                </p>
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('work-tracker.index', ['date' => $dateKey]) }}" class="text-xs font-semibold px-3 py-1.5 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-blue-600 dark:text-blue-400 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors inline-block">
                                🔍 View In Daily Tracker →
                            </a>
                        </div>
                    </div>

                    {{-- Day Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/50">
                                    <th class="px-6 py-3">Employee</th>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3 text-center">Listings</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3">Notes / Remarks</th>
                                    <th class="px-6 py-3 text-right">Logged At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                                @foreach($dayLogs as $log)
                                    <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-750/30 transition-colors">
                                        <td class="px-6 py-3.5 font-semibold text-gray-800 dark:text-white">
                                            👤 {{ $log->employee->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3.5 font-medium text-gray-900 dark:text-white">
                                            🏢 {{ $log->client->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-center font-bold text-emerald-600">
                                            {{ $log->listings_count }}
                                        </td>
                                        <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                            @if($log->is_done)
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                    ✓ Done
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                                    ⏳ Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-gray-600 dark:text-gray-300 text-xs">
                                            {{ $log->notes ?: '—' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-right text-xs text-gray-400 whitespace-nowrap">
                                            {{ $log->updated_at ? $log->updated_at->format('h:i A') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach

        @if(!$hasAnyLogs)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <h4 class="text-base font-bold text-gray-700 dark:text-gray-200">No work logs recorded in this month</h4>
                <p class="text-xs text-gray-400 mt-1">Select a different month/year or filter criteria above.</p>
            </div>
        @endif
    </div>
</div>
@endsection
