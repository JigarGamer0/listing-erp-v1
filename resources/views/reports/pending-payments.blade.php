@extends('layouts.app')
@section('title', 'Pending Payments')
@section('page-title', 'Outstanding Dues')

@section('content')
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Outstanding Balances</h2>
            <p class="text-sm text-gray-500">Cumulative Outstanding: ₹{{ number_format($totalPending, 2) }}</p>
        </div>
        <a href="{{ route('reports.export', ['type' => 'pending-payments']) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">📄 Export PDF</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Contact</th>
                <th class="px-5 py-3 text-center font-semibold text-gray-500 uppercase">Pending Cycles</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Outstanding Dues</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($clients as $c)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-5 py-4"><a href="{{ route('clients.show', $c->id) }}" class="font-medium text-blue-600 hover:underline">{{ $c->name }}</a></td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">{{ $c->mobile }}</td>
                        <td class="px-5 py-4 text-center">{{ $c->pending_months }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-red-600">₹{{ number_format($c->total_pending, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No outstanding payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
