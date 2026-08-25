@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Financial Reports')

@section('content')
<div class="fade-in">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Complete Hisab Report --}}
        <div class="bg-gradient-to-br from-indigo-550 to-blue-600 dark:from-indigo-900 dark:to-blue-900 rounded-2xl border border-indigo-100 dark:border-indigo-850 p-6 card-hover shadow-sm text-white">
            <h3 class="text-lg font-bold mb-2">📊 Complete Hisab Report</h3>
            <p class="text-sm text-white/80 mb-4">Complete accounting: All collections, expenses, employee salaries paid, advances given, and final fund balance sheets.</p>
            <a href="{{ route('reports.full-report') }}" class="text-white hover:underline text-sm font-bold flex items-center">Open Full Report →</a>
        </div>

        {{-- Collection Report --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 card-hover">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Collection Report</h3>
            <p class="text-sm text-gray-500 mb-4">View incoming client payments and collections by date range.</p>
            <a href="{{ route('reports.collection') }}" class="text-blue-600 hover:underline text-sm font-semibold flex items-center">Open Report →</a>
        </div>

        {{-- Expense Report --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 card-hover">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Expense Report</h3>
            <p class="text-sm text-gray-500 mb-4">Analyze organizational expenses categorized by source.</p>
            <a href="{{ route('reports.expense') }}" class="text-blue-600 hover:underline text-sm font-semibold flex items-center">Open Report →</a>
        </div>

        {{-- Profit Report --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 card-hover">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Profit & Loss Report</h3>
            <p class="text-sm text-gray-500 mb-4">Compare collection against expense trends to evaluate monthly net profits.</p>
            <a href="{{ route('reports.profit') }}" class="text-blue-600 hover:underline text-sm font-semibold flex items-center">Open Report →</a>
        </div>

        {{-- Pending Payments --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 card-hover">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Outstanding Dues</h3>
            <p class="text-sm text-gray-500 mb-4">Identify clients with pending balances and overdue billing cycles.</p>
            <a href="{{ route('reports.pending-payments') }}" class="text-blue-600 hover:underline text-sm font-semibold flex items-center">Open Report →</a>
        </div>

        {{-- Client Growth --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 card-hover">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Client Growth</h3>
            <p class="text-sm text-gray-500 mb-4">Track registration trends and onboarding stats over time.</p>
            <a href="{{ route('reports.client-growth') }}" class="text-blue-600 hover:underline text-sm font-semibold flex items-center">Open Report →</a>
        </div>
    </div>
</div>
@endsection
