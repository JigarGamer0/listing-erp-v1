@extends('layouts.app')
@section('title', 'Activity Logs')
@section('page-title', 'System Audit Trail')

@section('content')
<div class="fade-in">
    {{-- Filter Panel --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]"><label class="block text-xs font-medium text-gray-500 mb-1">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Action description..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></div>
            <div class="w-48"><label class="block text-xs font-medium text-gray-500 mb-1">User</label><select name="causer_id" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm outline-none"><option value="">All Users</option>@foreach($users as $u)<option value="{{ $u->id }}" {{ request('causer_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach</select></div>
            <button type="submit" class="px-5 py-2.5 bg-gray-800 dark:bg-gray-600 text-white text-sm font-medium rounded-xl">Filter</button>
        </form>
    </div>

    {{-- Activity Logs Timeline --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Timestamp</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actor</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Changes (Old ↔ New)</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($activities as $act)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $act->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">{{ $act->causer?->name ?? 'System' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $act->description }}</td>
                            <td class="px-5 py-4 text-xs">
                                @if(isset($act->properties['attributes']) || isset($act->properties['old']))
                                    <div class="space-y-1">
                                        @foreach($act->properties['attributes'] ?? [] as $key => $newVal)
                                            @php $oldVal = $act->properties['old'][$key] ?? 'N/A'; @endphp
                                            @if($oldVal !== $newVal)
                                                <div class="p-1.5 bg-gray-50 dark:bg-gray-700/40 rounded flex flex-wrap gap-1 items-center">
                                                    <span class="font-semibold text-gray-500">{{ $key }}:</span>
                                                    <span class="text-red-600 line-through">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</span>
                                                    <span class="text-gray-400">→</span>
                                                    <span class="text-green-600 font-medium">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-gray-400">No activities logged.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">{{ $activities->links() }}</div>
    </div>
</div>
@endsection
