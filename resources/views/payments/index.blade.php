@extends('layouts.app')
@section('title', 'Payments')
@section('page-title', 'Payment History')

@section('content')
<div class="fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]"><label class="block text-xs font-medium text-gray-500 mb-1">Search Client</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Client name..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">From</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">To</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></div>
            <button type="submit" class="px-5 py-2.5 bg-gray-800 dark:bg-gray-600 text-white text-sm font-medium rounded-xl">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Received By</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($payments as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $p->payment_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3"><a href="{{ route('clients.show', $p->client_id) }}" class="text-sm font-medium text-blue-600 hover:underline">{{ $p->client->name ?? 'N/A' }}</a></td>
                            <td class="px-5 py-3 text-right text-sm font-semibold text-emerald-600">₹{{ number_format($p->amount, 2) }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $p->receivedByUser?->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
