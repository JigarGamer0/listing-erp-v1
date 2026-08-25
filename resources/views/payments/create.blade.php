@extends('layouts.app')
@section('title', 'Receive Payment')
@section('page-title', 'Receive Payment — ' . $client->name)

@section('content')
<div class="max-w-2xl mx-auto fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        {{-- Client Info --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">{{ $client->name }}</h3>
                    <p class="text-sm text-gray-500">Package: ₹{{ number_format($client->current_package, 2) }}/month</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Outstanding</p>
                    <p class="text-xl font-bold text-red-600">₹{{ number_format($outstandingBalance, 2) }}</p>
                    @if($advanceBalance > 0)
                        <p class="text-sm text-emerald-600">Advance: ₹{{ number_format($advanceBalance, 2) }}</p>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('payments.store', $client) }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (₹) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" required step="0.01" min="1"
                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-lg font-semibold" placeholder="Enter amount">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method *</label>
                        <select name="payment_method" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reference Number</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none" placeholder="Transaction ID / Cheque No">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('clients.show', $client) }}" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-lg shadow-emerald-600/20 transition-all">💰 Receive Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection
