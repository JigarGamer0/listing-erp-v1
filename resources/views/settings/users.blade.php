@extends('layouts.app')
@section('title', 'User Administration')
@section('page-title', 'User Administration')

@section('content')
<div class="fade-in max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white font-poppins">User Accounts</h2>
        <div x-data="{ open: false }">
            <button @click="open = true" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg transition-all">+ Add User</button>

            {{-- Modal container using Alpine --}}
            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-transition>
                <div @click.outside="open = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Create System User</h3>
                    <form method="POST" action="{{ route('settings.users.create') }}">
                        @csrf
                        <div class="space-y-4">
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Name</label><input type="text" name="name" required class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none"></div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Email</label><input type="email" name="email" required class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none"></div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Username</label><input type="text" name="username" required class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none"></div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Password</label><input type="password" name="password" required class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none"></div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Role</label>
                                <select name="role" required class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none">
                                    <option value="Admin">Admin (Limited)</option>
                                    <option value="Main Admin">Main Admin (Full Access)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end space-x-3">
                            <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm shadow">Create User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Username</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($users as $u)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $u->name }}</p>
                            <p class="text-xs text-gray-500">{{ $u->email }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $u->username }}</td>
                        <td class="px-5 py-4 text-sm"><span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">{{ $u->roles->first()?->name ?? '—' }}</span></td>
                        <td class="px-5 py-4 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $u->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($u->status) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
