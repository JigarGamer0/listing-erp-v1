@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Alert Center')

@section('content')
<div class="fade-in max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Notifications</h2>
            <p class="text-sm text-gray-500">Inbox updates</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-50 text-blue-600 text-sm font-semibold rounded-xl hover:bg-blue-100 transition-all">✓ Mark All as Read</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($notifications as $notif)
            <div class="p-5 flex items-start justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ is_null($notif->read_at) ? 'bg-blue-50/20 dark:bg-blue-900/10' : '' }}">
                <div class="flex space-x-3">
                    <div class="w-2.5 h-2.5 rounded-full mt-1.5 {{ is_null($notif->read_at) ? 'bg-blue-600' : 'bg-transparent' }}"></div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white">{{ $notif->title }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ $notif->message }}</p>
                        <p class="text-xs text-gray-400 mt-2">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @if(is_null($notif->read_at))
                    <form method="POST" action="{{ route('notifications.read', $notif) }}">
                        @csrf
                        <button type="submit" class="p-1 hover:bg-gray-200 rounded-lg text-xs font-semibold text-blue-600">Mark read</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-gray-400 text-sm">All caught up! No notifications.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
