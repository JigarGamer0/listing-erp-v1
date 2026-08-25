@extends('layouts.app')
@section('title', 'Collection Report')
@section('page-title', 'Collection Report')

@section('content')
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Collection Details</h2>
            <p class="text-sm text-gray-500">Total Collection: ₹{{ number_format($totalCollection, 2) }}</p>
        </div>
        <a href="{{ route('reports.export', ['type' => 'collection', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">📄 Export PDF</a>
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
                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase">Method</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase">Amount</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($payments as $p)
                    <tr>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $p->payment_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $p->client->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ ucfirst($p->payment_method) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600">₹{{ number_format($p->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No collection records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
