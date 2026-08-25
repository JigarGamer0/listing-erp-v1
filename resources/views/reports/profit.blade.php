@extends('layouts.app')
@section('title', 'Profit & Loss')
@section('page-title', 'Profit & Loss Statement')

@section('content')
<div class="fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Total Collections</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">₹{{ number_format($totalCollection, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Total Expenses</p>
                <p class="text-2xl font-bold text-red-600 mt-1">₹{{ number_format($totalExpense, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Net Profit</p>
                <p class="text-2xl font-bold {{ $totalProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">₹{{ number_format($totalProfit, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                <th class="px-5 py-3 text-left font-semibold text-gray-500 uppercase">Month</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Collection</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Expense</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-500 uppercase">Net Profit</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($monthlyData as $data)
                    <tr>
                        <td class="px-5 py-3 text-gray-800 dark:text-white font-medium">{{ $data['month'] }}</td>
                        <td class="px-5 py-3 text-right text-emerald-600 font-medium">₹{{ number_format($data['collection'], 2) }}</td>
                        <td class="px-5 py-3 text-right text-red-600 font-medium">-₹{{ number_format($data['expense'], 2) }}</td>
                        <td class="px-5 py-3 text-right font-bold {{ $data['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₹{{ number_format($data['profit'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
