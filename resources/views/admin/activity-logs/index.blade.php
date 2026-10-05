@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Activity Log</h2>
        <p class="text-sm text-gray-500">{{ $logs->total() }} record(s)</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All</option>
                    @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action')==$a ? 'selected':'' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
            </div>
            <button type="submit" class="px-4 py-2 text-white rounded-lg text-sm" style="background:var(--sidebar-bg)">Filter</button>
            @if(request()->hasAny(['action','date_from','date_to']))
            <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-amber-100 bg-amber-50/40 p-4">
        <form method="POST" action="{{ route('admin.activity-logs.purge') }}" class="flex flex-wrap gap-3 items-end"
              onsubmit="return confirm('Permanently delete every log entry older than the selected date? This cannot be undone.')">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Purge logs older than</label>
                <input type="date" name="before_date" required
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
            </div>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm">Purge Old Logs</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        @if($logs->isEmpty())
        <div class="px-5 py-16 text-center text-gray-400 text-sm">No activity recorded yet.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">When</th>
                        <th class="px-5 py-3 text-left">User</th>
                        <th class="px-5 py-3 text-left">Action</th>
                        <th class="px-5 py-3 text-left">Description</th>
                        <th class="px-5 py-3 text-left">IP</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-gray-800">{{ $log->user_name ?? 'Unknown' }}</span>
                            @if($log->user_role)
                            <span class="text-xs text-gray-400 block">{{ $log->user_role }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $actionColors = str_contains($log->action, 'failed') ? 'bg-red-50 text-red-700'
                                    : (str_contains($log->action, 'delete') || str_contains($log->action, 'withdraw') ? 'bg-amber-50 text-amber-700'
                                    : 'bg-blue-50 text-blue-700');
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $actionColors }}">{{ $log->action }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-700">{{ $log->description }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $log->ip_address }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.activity-logs.destroy', $log) }}"
                                  onsubmit="return confirm('Delete this log entry?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
