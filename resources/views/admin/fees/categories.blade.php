@extends('layouts.admin')
@section('title', 'Fee Categories')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Add Category</h3>
        <form method="POST" action="{{ route('admin.fees.categories.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Tuition Fee" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <button type="submit" class="btn-primary w-full py-2">Add Category</button>
        </form>
    </div>
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Categories ({{ $categories->count() }})</h3>
        </div>
        @forelse($categories as $cat)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50">
            <div>
                <p class="text-sm font-semibold text-gray-800">{{ $cat->name }}</p>
                <p class="text-xs text-gray-400">{{ $cat->description ?? 'No description' }} • {{ $cat->fee_structures_count }} structure(s)</p>
            </div>
            <form method="POST" action="{{ route('admin.fees.categories.destroy', $cat) }}"
                onsubmit="return confirm('Delete {{ $cat->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
            </form>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-gray-400 text-sm">No categories yet.</div>
        @endforelse
    </div>
</div>
@endsection
