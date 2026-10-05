@extends('layouts.admin')
@section('title', 'Examinations')
@section('content')

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Examinations</h2>
        <p class="text-sm text-gray-500">Academic Year: <strong>{{ $year?->name ?? 'Not set' }}</strong></p>
    </div>
    <div class="flex items-center gap-3">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-600">Viewing:</label>
            <select name="academic_year_id" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}" {{ (isset($year) && $year->id == $ay->id) ? 'selected' : '' }}>
                    {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
                </option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('admin.exams.grades') }}"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">
            Grade Scales
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Create Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Create Exam</h3>
        <form method="POST" action="{{ route('admin.exams.types.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Exam Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. End of Term 1" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <button type="submit" class="btn-primary w-full py-2">Create Exam</button>
        </form>
    </div>

    {{-- Exam List --}}
    <div class="lg:col-span-2 space-y-4">
        @forelse($examTypes as $exam)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-800">{{ $exam->name }}</h3>
                        @if($exam->is_published)
                            <span class="px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Published</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-500 rounded-full">Draft</span>
                        @endif
                    </div>
                    @if($exam->description)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $exam->description }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $exam->schedules->count() }} subject(s) scheduled</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <form method="POST" action="{{ route('admin.exams.types.publish', $exam) }}">
                        @csrf
                        <button type="submit"
                            class="text-xs px-3 py-1.5 border rounded-lg transition
                            {{ $exam->is_published ? 'border-orange-200 text-orange-600 hover:bg-orange-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                            {{ $exam->is_published ? 'Unpublish' : 'Publish' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.exams.types.destroy', $exam) }}"
                        onsubmit="return confirm('Delete this exam?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs px-3 py-1.5 border border-red-100 text-red-500 rounded-lg hover:bg-red-50">Delete</button>
                    </form>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.exams.schedule', $exam) }}"
                    class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Schedule
                </a>
                <a href="{{ route('admin.exams.marks', $exam) }}"
                    class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Enter Marks
                </a>
                <a href="{{ route('admin.exams.results', $exam) }}"
                    class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Results
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center text-gray-400">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm">No exams yet. Create your first exam above.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
