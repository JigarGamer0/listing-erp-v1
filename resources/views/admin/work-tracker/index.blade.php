@extends('layouts.app')
@section('title', 'Employee Daily Work Tracker — Listing ERP')
@section('page-title', 'Employee Work Tracker')

@section('content')
<div class="fade-in">
    {{-- Header & Date Filter --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Daily Work Tracker</h2>
            <p class="text-sm text-gray-500">Monitor employee daily listing counts and work completion logs.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('work-tracker.monthly-history') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                📜 Full Month History
            </a>
            <form method="GET" action="{{ route('work-tracker.index') }}" class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Date:</label>
                <input type="date" name="date" value="{{ $dateStr }}" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs focus:ring-2 focus:ring-blue-500">
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 space-x-4">
        <a href="{{ route('work-tracker.index', ['date' => $dateStr]) }}" class="py-3 px-4 text-sm font-bold text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400">
            📅 Daily Live Tracker
        </a>
        <a href="{{ route('work-tracker.monthly-history') }}" class="py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent">
            📜 Full Month History
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        {{-- Progress Circle/Percent Card --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm md:col-span-2">
            <div class="flex justify-between items-center mb-3">
                <p class="text-xs text-gray-400 uppercase font-medium">Daily Completion Progress</p>
                <span class="text-sm font-extrabold text-blue-600">{{ $progressPercent }}% Done</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 h-3.5 rounded-full overflow-hidden mb-2">
                <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ $completedClientsToday }} out of {{ $totalClientsAssigned }} assigned client works completed for {{ Carbon\Carbon::parse($dateStr)->format('d M Y') }}.
            </p>
        </div>

        {{-- Card 2: Total Listings today --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-medium">Total Listings Done</p>
            <p class="text-3xl font-extrabold text-green-600 mt-1">{{ number_format($totalListingsToday) }}</p>
            <p class="text-xs text-gray-500 mt-1">Listings created today</p>
        </div>

        {{-- Card 3: Active Employees count --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs text-gray-400 uppercase font-medium">Reporting Employees</p>
            <p class="text-3xl font-extrabold text-gray-800 dark:text-white mt-1">{{ count($trackerData) }}</p>
            <p class="text-xs text-gray-500 mt-1">Active with assigned clients</p>
        </div>
    </div>

    {{-- Employee Progress Cards --}}
    <div class="space-y-6">
        @forelse($trackerData as $empId => $data)
            @php
                $empPercent = $data['total_count'] > 0 ? round(($data['done_count'] / $data['total_count']) * 100) : 0;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm p-6">
                {{-- Employee Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-750 pb-4 mb-4 gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <span>👤 {{ $data['employee_name'] }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium">
                                {{ $data['done_count'] }}/{{ $data['total_count'] }} Clients Done
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Total Listings created: <span class="font-bold text-gray-800 dark:text-white">{{ $data['listings_sum'] }}</span></p>
                    </div>
                    <div class="w-full sm:w-48">
                        <div class="flex justify-between items-center text-xs mb-1">
                            <span class="font-semibold text-gray-500">Progress</span>
                            <span class="font-bold text-indigo-600">{{ $empPercent }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $empPercent }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Assignments Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-700/20 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <th class="px-4 py-2.5">Client Name</th>
                                <th class="px-4 py-2.5">Platform</th>
                                <th class="px-4 py-2.5">Listings Count</th>
                                <th class="px-4 py-2.5">Daily Status</th>
                                <th class="px-4 py-2.5">Remarks / Notes</th>
                                <th class="px-4 py-2.5 text-right">Log Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 dark:divide-gray-750">
                            @foreach($data['clients'] as $client)
                                <tr class="hover:bg-gray-50/20 dark:hover:bg-gray-900/5 transition-colors text-sm">
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-800 dark:text-white">
                                        {{ $client['client_name'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($client['gst_platform'] === 'flipkart')
                                            <span class="text-xs bg-blue-50 text-blue-700 dark:bg-blue-900/20 px-2 py-0.5 rounded font-semibold">Flipkart ({{ $client['gst_count'] }} GSTs)</span>
                                        @elseif($client['gst_platform'] === 'meesho')
                                            <span class="text-xs bg-pink-50 text-pink-700 dark:bg-pink-900/20 px-2 py-0.5 rounded font-semibold">Meesho ({{ $client['gst_count'] }} GSTs)</span>
                                        @else
                                            <span class="text-xs bg-gray-55 dark:bg-gray-700 px-2 py-0.5 rounded">Other</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-800 dark:text-white">
                                        {{ $client['listings'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($client['is_done'])
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-105 text-green-700 dark:bg-green-950/30 dark:text-green-300">
                                                ✓ Completed
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-300">
                                                ⏳ Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                        {{ $client['notes'] ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-xs text-gray-400">
                                        {{ $client['logged_at'] ? $client['logged_at']->format('h:i A') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-8 text-center text-gray-400">
                No active employee client assignments found.
            </div>
        @endforelse
    </div>
</div>
@endsection
