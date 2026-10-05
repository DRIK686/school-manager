@extends('layouts.admin')
@section('title', 'Staff')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">All Staff</h2>
        <p class="text-sm text-gray-500">{{ $staff->total() }} member(s)</p>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="btn-primary px-5 py-2.5 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Staff
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        </div>
        <div class="w-44">
            <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
            <select name="role_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary px-4 py-2 text-sm">Filter</button>
        @if(request()->hasAny(['search','role_id']))
            <a href="{{ route('admin.staff.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @if($staff->count())
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Staff Member</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee ID</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Designation</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $member)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="px-6 py-3">
                    <div class="flex items-center gap-3">
                        @if($member->profile_photo)
                            <img src="{{ asset('storage/'.$member->profile_photo) }}"
                                class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                style="background:var(--sidebar-bg)">
                                {{ strtoupper(substr($member->name,0,1)) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-800">{{ $member->name }}</p>
                            <p class="text-xs text-gray-400">{{ $member->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                        {{ $member->staffProfile?->employee_id ?? '—' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                        {{ $member->role?->name }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600 text-sm">
                    {{ $member->staffProfile?->designation ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $member->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.staff.show', $member) }}"
                        class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $staff->links() }}</div>
    @else
    <div class="py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <p class="text-gray-400 text-sm">No staff members yet.</p>
        <a href="{{ route('admin.staff.create') }}" class="mt-3 inline-block btn-primary px-5 py-2 text-sm">Add First Staff</a>
    </div>
    @endif
</div>
@endsection
