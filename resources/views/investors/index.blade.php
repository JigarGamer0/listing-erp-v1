@extends('layouts.app')
@section('title', 'Investors')
@section('page-title', 'Investors')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Investors</h2>
            <p class="text-sm text-gray-500">Manage investor profiles before adding investments</p>
        </div>
        <button onclick="openModal('add-investor-modal')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all shadow-sm flex items-center gap-1.5">
            ➕ Add Investor
        </button>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('investors.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="w-64">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or mobile..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none">
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all">Filter</button>
            <a href="{{ route('investors.index') }}" class="px-5 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-all">Reset</a>
        </form>
    </div>

    {{-- Investors Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($investors as $investor)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm">{{ strtoupper(substr($investor->name, 0, 2)) }}</div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white">{{ $investor->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $investor->mobile ?? '—' }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $investor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                        {{ ucfirst($investor->status) }}
                    </span>
                </div>

                @if($investor->email)
                    <p class="text-xs text-gray-500 mb-2">✉️ {{ $investor->email }}</p>
                @endif

                <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="text-center">
                        <p class="text-xs text-gray-400">Total</p>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $investor->investments_count }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-400">Uncleared</p>
                        <p class="text-sm font-bold text-red-600">{{ $investor->uncleared_count }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-400">Invested</p>
                        <p class="text-sm font-bold text-indigo-600">₹{{ number_format($investor->total_invested, 0) }}</p>
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button onclick="openEditModal({{ json_encode($investor) }})" class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg hover:bg-gray-200 transition-all text-center">
                        ✏️ Edit
                    </button>
                    <a href="{{ route('investments.index', ['investor_id' => $investor->id]) }}" class="flex-1 px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-lg hover:bg-indigo-100 transition-all text-center">
                        📊 Investments
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <p class="text-lg font-medium mb-1">No investors found</p>
                <p class="text-sm">Click "Add Investor" to create one.</p>
            </div>
        @endforelse
    </div>

    @if($investors->hasPages())
        <div class="mt-6">
            {{ $investors->links() }}
        </div>
    @endif
</div>

{{-- Add Investor Modal --}}
<div id="add-investor-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-xl relative">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Add New Investor</h3>
        <form method="POST" action="{{ route('investors.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Investor Name *</label>
                    <input type="text" name="name" required placeholder="Full name..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Mobile</label>
                    <input type="text" name="mobile" placeholder="Mobile number..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" placeholder="Email..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Address</label>
                    <textarea name="address" placeholder="Address..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Notes</label>
                    <textarea name="notes" placeholder="Any notes..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" rows="2"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('add-investor-modal')" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">Save Investor</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Investor Modal --}}
<div id="edit-investor-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-xl relative">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Edit Investor</h3>
        <form method="POST" id="edit-investor-form">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Name *</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Mobile</label>
                    <input type="text" name="mobile" id="edit_mobile" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Address</label>
                    <textarea name="address" id="edit_address" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Notes</label>
                    <textarea name="notes" id="edit_notes" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                    <select name="status" id="edit_status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('edit-investor-modal')" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">Update</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openEditModal(investor) {
    document.getElementById('edit-investor-form').action = '/investors/' + investor.id;
    document.getElementById('edit_name').value = investor.name;
    document.getElementById('edit_mobile').value = investor.mobile || '';
    document.getElementById('edit_email').value = investor.email || '';
    document.getElementById('edit_address').value = investor.address || '';
    document.getElementById('edit_notes').value = investor.notes || '';
    document.getElementById('edit_status').value = investor.status;
    openModal('edit-investor-modal');
}
</script>
@endpush
@endsection
