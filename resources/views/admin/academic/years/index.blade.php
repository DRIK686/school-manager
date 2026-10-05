@extends('layouts.admin')
@section('title', 'Academic Years')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Add Academic Year</h3>
        <form method="POST" action="{{ route('admin.academic-years.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Year Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. 2025/2026" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Start Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('start_date') border-red-400 @enderror">
                @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('end_date') border-red-400 @enderror">
                @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary w-full py-2">Create Year</button>
        </form>
    </div>
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Academic Years</h3>
        </div>
        @forelse($years as $year)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50 transition">
            <div class="flex items-center gap-3">
                @if($year->is_current)
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full text-white" style="background:var(--sidebar-bg)">CURRENT</span>
                @else
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">INACTIVE</span>
                @endif
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $year->name }}</p>
                    <p class="text-xs text-gray-400">{{ $year->start_date->format('d M Y') }} — {{ $year->end_date->format('d M Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if(!$year->is_current)
                <form method="POST" action="{{ route('admin.academic-years.set-current', $year) }}">
                    @csrf
                    <button type="submit" class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition">Set Current</button>
                </form>
                <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" onsubmit="return confirm('Delete this year?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs px-3 py-1.5 border border-red-100 rounded-lg hover:bg-red-50 text-red-500 transition">Delete</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-gray-400 text-sm">No academic years yet.</div>
        @endforelse
    </div>
</div>
@endsection
