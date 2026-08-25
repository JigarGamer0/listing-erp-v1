@extends('layouts.app')
@section('title', 'Expense Report')
@section('page-title', 'Expense Report')

@section('content')
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Expense Details</h2>
            <p class="text-sm text-gray-500">Total Expense: ₹{{ number_format($totalExpense, 2) }}</p>
        </div>
        <a href="{{ route('reports.export', ['type' => 'expense', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">📄 Export PDF</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="block text-xs font-medium text-gray-500 mb-1">From</label><input type="date" name="date_from" value="{{ $dateFrom }}" class="px-4 py-2 border rounded-xl dark:bg-gray-700 dark:text-white text-sm outline-none"></div>
            <div><label class="block text-xs font-medium text-gray-500 mb-1">To</label><input type="date" name="date_to" value="{{ $dateTo }}" class="px-4 py-2 border rounded-xl dark:bg-gray-700 dark:text-white text-sm outline-none"></div>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-all">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase">Date</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase">Title</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase">Category</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase">Amount</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($expenses as $e)
                    <tr>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $e->expense_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $e->title }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $e->category->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-red-600">₹{{ number_format($e->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No expense records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
