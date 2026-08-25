@extends('layouts.app')
@section('title', 'Edit Client')
@section('page-title', 'Edit Client: ' . $client->name)

@section('content')
<div class="max-w-4xl mx-auto fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('clients.update', $client) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Name *</label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex justify-between items-center">
                        <span>Mobile Number *</span>
                        <button type="button" id="add-secondary-mobile" class="text-xs text-blue-600 hover:underline flex items-center gap-0.5">
                            <span>+ Add secondary</span>
                        </button>
                    </label>
                    <div class="space-y-2">
                        <input type="text" name="mobile" value="{{ old('mobile', $client->mobile) }}" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <div id="secondary-mobile-container" class="{{ old('mobile_secondary', $client->mobile_secondary) ? '' : 'hidden' }}">
                            <input type="text" name="mobile_secondary" value="{{ old('mobile_secondary', $client->mobile_secondary) }}" placeholder="Secondary Mobile Number" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Work Location</label>
                    <select name="work_location" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="our_office" {{ $client->work_location == 'our_office' ? 'selected' : '' }}>Our Office</option>
                        <option value="client_office" {{ $client->work_location == 'client_office' ? 'selected' : '' }}>Client Office</option>
                        <option value="hybrid" {{ $client->work_location == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
            </div>
            <div class="mt-5">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">{{ old('address', $client->address) }}</textarea>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('clients.show', $client) }}" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all">Update Client</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addSecondaryMobileBtn = document.getElementById('add-secondary-mobile');
    const secondaryMobileContainer = document.getElementById('secondary-mobile-container');
    if (addSecondaryMobileBtn && secondaryMobileContainer) {
        addSecondaryMobileBtn.addEventListener('click', function() {
            secondaryMobileContainer.classList.toggle('hidden');
        });
    }
});
</script>
@endpush
@endsection
