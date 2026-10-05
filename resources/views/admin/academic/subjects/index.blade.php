@extends('layouts.admin')
@section('title', 'Subjects')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit" x-data="{ color: '#6366f1' }">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Add Subject</h3>
        <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Subject Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Mathematics" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. MATH" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    <option value="theory">Theory</option>
                    <option value="practical">Practical</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Color Label</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="color" x-model="color" value="{{ old('color','#6366f1') }}"
                        class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5">
                    <input type="text" x-model="color" maxlength="7"
                        class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary w-full py-2">Add Subject</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Subjects ({{ $subjects->count() }})</h3>
        </div>
        @forelse($subjects as $subject)
        <div class="border-b border-gray-50" x-data="{ editing: false, color: '{{ $subject->color }}' }">
            <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-10 rounded-full flex-shrink-0" style="background:{{ $subject->color }}"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $subject->name }}
                            <span class="ml-1 text-xs font-mono bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">{{ $subject->code }}</span>
                        </p>
                        <p class="text-xs text-gray-400">
                            <span class="px-1.5 py-0.5 rounded text-xs {{ $subject->type === 'practical' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($subject->type) }}
                            </span>
                            &nbsp;{{ $subject->classes_count }} class(es)
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="editing=!editing"
                        class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition">
                        Edit
                    </button>
                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                        onsubmit="return confirm('Delete {{ $subject->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs px-3 py-1.5 border border-red-100 rounded-lg hover:bg-red-50 text-red-500 transition">Delete</button>
                    </form>
                </div>
            </div>
            {{-- Inline edit --}}
            <div x-show="editing" x-cloak class="px-6 pb-4 bg-gray-50 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="flex flex-wrap gap-3 items-end pt-3">
                    @csrf @method('PUT')
                    <div class="flex-1 min-w-32">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                        <input type="text" name="name" value="{{ $subject->name }}" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Code</label>
                        <input type="text" name="code" value="{{ $subject->code }}" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>
                    <div class="w-32">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                            <option value="theory" {{ $subject->type==='theory'?'selected':'' }}>Theory</option>
                            <option value="practical" {{ $subject->type==='practical'?'selected':'' }}>Practical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                        <input type="color" name="color" x-model="color"
                            class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5">
                    </div>
                    <button type="submit" class="btn-primary px-4 py-2 text-sm">Save</button>
                    <button type="button" @click="editing=false" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-100">Cancel</button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-gray-400 text-sm">No subjects yet.</div>
        @endforelse
    </div>
</div>
@endsection
