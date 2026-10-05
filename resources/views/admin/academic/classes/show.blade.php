@extends('layouts.admin')
@section('title', $class->name . ' — Details')
@section('content')
<div class="mb-4">
    <a href="{{ route('admin.classes.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Classes
    </a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Sections --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Sections in {{ $class->name }}</h3>
            <span class="text-xs text-gray-400">{{ $sections->count() }} section(s)</span>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
            <form method="POST" action="{{ route('admin.classes.sections.store', $class) }}" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section Name</label>
                    <input type="text" name="name" placeholder="e.g. A, Gold" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Capacity</label>
                    <input type="number" name="capacity" value="30" min="1" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <button type="submit" class="btn-primary px-4 py-2">Add</button>
            </form>
        </div>
        @forelse($sections as $section)
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-50 hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background:var(--accent)">
                    {{ strtoupper(substr($section->name,0,1)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">Section {{ $section->name }}</p>
                    <p class="text-xs text-gray-400">Capacity: {{ $section->capacity }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.classes.sections.destroy', [$class, $section]) }}"
                onsubmit="return confirm('Remove this section?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
            </form>
        </div>
        @empty
        <div class="px-6 py-8 text-center text-gray-400 text-sm">No sections yet.</div>
        @endforelse
    </div>

    {{-- Subjects --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Subjects in {{ $class->name }}</h3>
            <span class="text-xs text-gray-400">{{ $classSubjects->count() }} subject(s)</span>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
            <form method="POST" action="{{ route('admin.classes.subjects.assign', $class) }}" class="space-y-3">
                @csrf
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Subject</label>
                        <select name="subject_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                            <option value="">Select subject...</option>
                            @foreach($allSubjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Teacher (optional)</label>
                        <select name="teacher_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                            <option value="">Unassigned</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-primary px-4 py-2 text-sm">Assign Subject</button>
            </form>
        </div>
        @forelse($classSubjects as $cs)
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-50 hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-10 rounded-full flex-shrink-0" style="background:{{ $cs->subject->color }}"></div>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $cs->subject->name }}</p>
                    <p class="text-xs text-gray-400">{{ ucfirst($cs->subject->type) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.classes.subjects.assign', $class) }}" class="flex items-center gap-1">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $cs->subject_id }}">
                    <select name="teacher_id" class="px-2 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2">
                        <option value="">Unassigned</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $cs->teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="text-xs font-medium" style="color:var(--accent)">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.classes.subjects.remove', [$class, $cs->subject]) }}"
                    onsubmit="return confirm('Remove this subject?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-8 text-center text-gray-400 text-sm">No subjects assigned yet.</div>
        @endforelse
    </div>
    {{-- Class Teacher --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Class Teacher for {{ $class->name }}</h3>
            <span class="text-xs text-gray-400">{{ $class->classTeacher?->name ?? 'Unassigned' }}</span>
        </div>
        <div class="px-6 py-4">
            <form method="POST" action="{{ route('admin.classes.update', $class) }}" class="flex gap-3 items-end">
                @csrf @method('PUT')
                <input type="hidden" name="name" value="{{ $class->name }}">
                <input type="hidden" name="numeric_order" value="{{ $class->numeric_order }}">
                <div class="flex-1 max-w-sm">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class Teacher</label>
                    <select name="class_teacher_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">Unassigned</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $class->class_teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary px-4 py-2 text-sm">Save</button>
            </form>
            <p class="text-xs text-gray-400 mt-2">The class teacher handles attendance, promotion, and report cards for this class. Subject teachers keep entering their own subject marks only.</p>
        </div>
    </div>
</div>
@endsection
