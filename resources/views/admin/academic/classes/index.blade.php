@extends('layouts.admin')
@section('title', 'Classes')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Add Class</h3>
        <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Basic 1, JHS 2" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sort Order <span class="text-red-500">*</span></label>
                <input type="number" name="numeric_order" value="{{ old('numeric_order', $classes->count() + 1) }}" min="1" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <button type="submit" class="btn-primary w-full py-2">Add Class</button>
        </form>
    </div>
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Classes ({{ $classes->count() }})</h3>
        </div>
        @forelse($classes as $class)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50 transition">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                    style="background:var(--sidebar-bg)">{{ $class->numeric_order }}</div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $class->name }}</p>
                    <p class="text-xs text-gray-400">{{ $class->sections_count }} section(s)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.classes.show', $class) }}"
                    class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition">Manage</a>
                <form method="POST" action="{{ route('admin.classes.destroy', $class) }}"
                    onsubmit="return confirm('Delete {{ $class->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs px-3 py-1.5 border border-red-100 rounded-lg hover:bg-red-50 text-red-500 transition">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-gray-400 text-sm">No classes yet.</div>
        @endforelse
    </div>
</div>
@endsection
