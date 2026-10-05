@extends('layouts.teacher')
@section('title','Exams')
@section('page-title','Exams & Marks')
@section('content')
<div class="space-y-4">
    @if(($academicYears ?? collect())->count() > 1)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Academic Year</label>
                <select name="academic_year_id" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}" {{ (isset($year) && $year->id == $ay->id) ? 'selected' : '' }}>
                        {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">
                {{ $year?->name ?? 'Current Year' }} — Exam Types
            </h2>
        </div>
        @if($examTypes->isEmpty())
        <div class="px-5 py-16 text-center text-gray-400 text-sm">No exams created yet.</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($examTypes as $exam)
            <div class="px-5 py-4 flex items-center justify-between gap-4">
                <div>
                    <p class="font-medium text-gray-800">{{ $exam->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $exam->schedules->count() }} subject(s) scheduled
                        @if($exam->is_published)
                            · <span class="text-green-600 font-medium">Published</span>
                        @else
                            · <span class="text-amber-600">Not published</span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('admin.exams.marks', $exam) }}"
                       class="text-xs px-3 py-1.5 rounded-lg text-white"
                       style="background:var(--sidebar-bg)">
                       Enter Marks
                    </a>
                    <a href="{{ route('admin.exams.results', $exam) }}"
                       class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                       Results
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
