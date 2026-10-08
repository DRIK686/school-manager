@extends('layouts.teacher')
@section('title', $exam->name . ' — Results')
@section('content')

<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('admin.exams.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Exams
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex gap-3 items-end">
        <div class="w-44">
            <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
            <select name="class_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">Select class...</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary px-5 py-2 text-sm">Load Results</button>
    </form>
</div>

@if($results->count())
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800">{{ $exam->name }} Results
            @if(!$exam->is_published)
                <span class="ml-2 text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full">Not Published</span>
            @endif
        </h3>
        <span class="text-xs text-gray-400">{{ $results->count() }} students</span>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500">#</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">Total</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">Average</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">Grade</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">Remark</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $row)
@php
    $tr = \App\Models\StudentTermReport::where('student_id', $row['student']->id)
        ->where('exam_type_id', $exam->id)
        ->where('academic_year_id', \App\Models\AcademicYear::current()?->id)
        ->first();
@endphp
<tbody x-data="{ showReport: false, showPreview: false }">
    {{-- Main result row --}}
    <tr class="border-b border-gray-100 hover:bg-gray-50">
        <td class="px-4 py-3 text-center text-gray-400 text-sm">{{ $loop->iteration }}</td>
        <td class="px-4 py-3">
            <p class="font-medium text-gray-800">{{ $row['student']->full_name }}</p>
            <p class="text-xs text-gray-400">{{ $row['student']->admission_no }}</p>
        </td>
        <td class="px-4 py-3 text-center text-gray-700">
            {{ $row['total'] }} / {{ $row['max_total'] }}
        </td>
        <td class="px-4 py-3 text-center font-bold" style="color:var(--sidebar-bg)">
            {{ $row['avg'] }}%
        </td>
        <td class="px-4 py-3 text-center">
            <span class="font-bold text-lg {{ $row['grade']?->grade === 'F9' ? 'text-red-500' : 'text-gray-800' }}">
                {{ $row['grade']?->grade ?? '—' }}
            </span>
        </td>
        <td class="px-4 py-3 text-center">
            <span class="text-xs px-2 py-0.5 rounded-full
                {{ $row['grade']?->remark === 'Excellent' ? 'bg-green-100 text-green-700' :
                   ($row['grade']?->remark === 'Fail' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-700') }}">
                {{ $row['grade']?->remark ?? '—' }}
            </span>
        </td>
        <td class="px-4 py-3">
            <div class="flex gap-2">
                <button type="button" @click="showReport=!showReport; showPreview=false"
                    :class="showReport ? 'bg-blue-50 border-blue-300 text-blue-700' : 'border-gray-200 text-gray-600'"
                    class="text-xs px-3 py-1.5 border rounded-lg hover:bg-gray-50 flex items-center gap-1 whitespace-nowrap">
                    ✏️ Term Report
                </button>
                <button type="button" @click="showPreview=!showPreview; showReport=false"
                    :class="showPreview ? 'bg-green-50 border-green-300 text-green-700' : 'border-gray-200 text-gray-600'"
                    class="text-xs px-3 py-1.5 border rounded-lg hover:bg-gray-50 flex items-center gap-1 whitespace-nowrap">
                    📄 Report Card
                </button>
            </div>
        </td>
    </tr>

    {{-- Term Report Panel --}}
    <tr x-show="showReport" x-cloak>
        <td colspan="7" class="px-4 py-4 bg-blue-50 border-b border-blue-100">
            <form method="POST" action="{{ route('admin.exams.term-report.save', [$exam, $row['student']]) }}">
                @csrf
                <p class="text-xs font-semibold text-blue-800 mb-3 uppercase tracking-wide">
                    ✏️ Term Report — {{ $row['student']->full_name }}
                </p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Conduct</label>
                        <input type="text" name="conduct" value="{{ $tr?->conduct }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                               placeholder="e.g. Very Good">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Talent / Interest</label>
                        <input type="text" name="talent_interest" value="{{ $tr?->talent_interest }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                               placeholder="e.g. Music, Sports">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Promoted To</label>
                        <input type="text" name="promoted_to" value="{{ $tr?->promoted_to }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                               placeholder="e.g. JHS 2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Attendance (Present)</label>
                        <input type="number" name="attendance_present" value="{{ $tr?->attendance_present }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                               placeholder="e.g. 69">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Attendance (Total Days)</label>
                        <input type="number" name="attendance_total" value="{{ $tr?->attendance_total }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                               placeholder="e.g. 71">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vacation Date</label>
                        <input type="date" name="vacation_date" value="{{ $tr?->vacation_date?->format('Y-m-d') }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Re-opening Date</label>
                        <input type="date" name="reopening_date" value="{{ $tr?->reopening_date?->format('Y-m-d') }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Class Teacher Name</label>
                        <input type="text" name="class_teacher" value="{{ $tr?->class_teacher }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                               placeholder="e.g. Mr. Kankam">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Principal Name</label>
                        <input type="text" name="principal" value="{{ $tr?->principal }}"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                               placeholder="e.g. Mrs. Ampofo">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class Teacher's General Remarks</label>
                    <textarea name="teacher_remarks" rows="3"
                              class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none bg-white"
                              placeholder="General remarks about the student's performance...">{{ $tr?->teacher_remarks }}</textarea>
                </div>
                <div class="flex gap-3 items-center">
                    <button type="submit"
                            class="px-4 py-1.5 text-white rounded text-xs font-medium"
                            style="background:var(--sidebar-bg)">
                        Save Term Report
                    </button>
                    <button type="button" @click="showReport=false"
                            class="px-4 py-1.5 border border-gray-200 rounded text-xs text-gray-600">
                        Close
                    </button>
                </div>
            </form>
        </td>
    </tr>

    {{-- Report Card Preview Panel --}}
    <tr x-show="showPreview" x-cloak>
        <td colspan="7" class="px-4 py-4 bg-green-50 border-b border-green-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-green-800 uppercase tracking-wide">
                    📄 Report Card Preview — {{ $row['student']->full_name }}
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.exams.report-card', [$exam, $row['student']]) }}"
                       class="text-xs px-3 py-1.5 text-white rounded font-medium flex items-center gap-1"
                       style="background:var(--sidebar-bg)">
                        ⬇ Download PDF
                    </a>
                    <form method="POST" action="{{ route('admin.exams.report-card.email', [$exam, $row['student']]) }}"
                          onsubmit="return confirm('Email this report card to the parent on file?')">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 border border-green-200 bg-white text-green-700 rounded font-medium flex items-center gap-1">
                            ✉️ Email to Parent
                        </button>
                    </form>
                    <button type="button" @click="showPreview=false"
                            class="text-xs px-3 py-1.5 border border-gray-200 rounded text-gray-600">
                        Close
                    </button>
                </div>
            </div>
            {{-- Inline preview --}}
            <div class="bg-white border border-gray-200 rounded-lg p-5 text-xs font-mono" style="font-family:Arial,sans-serif">
                {{-- Header --}}
                <div class="text-center border-b-2 border-gray-800 pb-3 mb-3">
                    <div class="font-bold text-sm uppercase">{{ $school->school_name }}</div>
                    <div class="font-semibold mt-0.5">{{ strtoupper($exam->name) }} REPORT</div>
                </div>
                {{-- Student Info --}}
                <div class="grid grid-cols-2 gap-x-8 gap-y-1 mb-3 text-xs">
                    <div>NAME: <strong>{{ strtoupper($row['student']->full_name) }}</strong></div>
                    <div>ID: <strong>{{ $row['student']->admission_no }}</strong></div>
                    <div>CLASS: <strong>{{ strtoupper(optional($classes->firstWhere('id', $classId))->name ?? $row['student']->schoolClass?->name) }}</strong></div>
                    <div>ATTENDANCE: <strong>{{ $tr?->attendance_present ?? '—' }} / {{ $tr?->attendance_total ?? '—' }}</strong></div>
                    <div>PROMOTED TO: <strong>{{ $tr?->promoted_to ?? '—' }}</strong></div>
                    <div>POSITION: <strong>{{ $row['position'] ?? '—' }} / {{ $row['total_students'] ?? '—' }}</strong></div>
                </div>
                {{-- Marks table --}}
                <table class="w-full text-xs border-collapse mb-3">
                    <thead>
                        <tr style="background:var(--sidebar-bg);color:white">
                            <th class="border border-gray-300 px-2 py-1 text-left">Subject</th>
                            <th class="border border-gray-300 px-2 py-1 text-center">SBA 50%</th>
                            <th class="border border-gray-300 px-2 py-1 text-center">Exam 50%</th>
                            <th class="border border-gray-300 px-2 py-1 text-center">Total</th>
                            <th class="border border-gray-300 px-2 py-1 text-center">Grade</th>
                            <th class="border border-gray-300 px-2 py-1 text-center">Avg</th>
                            <th class="border border-gray-300 px-2 py-1 text-left">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($row['marks'] as $scheduleId => $mark)
                        @php $schedule = $row['schedules']->firstWhere('id', $scheduleId); @endphp
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="border border-gray-200 px-2 py-1">{{ $schedule?->subject->name ?? '—' }}</td>
                            <td class="border border-gray-200 px-2 py-1 text-center">{{ $mark->is_absent ? 'ABS' : ($mark->sba_score ?? '—') }}</td>
                            <td class="border border-gray-200 px-2 py-1 text-center">{{ $mark->is_absent ? 'ABS' : ($mark->exam_score ?? '—') }}</td>
                            <td class="border border-gray-200 px-2 py-1 text-center font-bold">{{ $mark->is_absent ? 'ABS' : ($mark->marks_obtained ?? '—') }}</td>
                            <td class="border border-gray-200 px-2 py-1 text-center font-bold" style="color:var(--sidebar-bg)">{{ $mark->grade ?? '—' }}</td>
                            <td class="border border-gray-200 px-2 py-1 text-center">{{ $mark->class_average ?? '—' }}</td>
                            <td class="border border-gray-200 px-2 py-1">{{ $mark->remarks ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- Bottom info --}}
                @if($tr?->teacher_remarks)
                <div class="border border-gray-200 rounded p-2 mb-2">
                    <strong>Remarks:</strong> {{ $tr->teacher_remarks }}
                </div>
                @endif
                <div class="grid grid-cols-2 gap-4 mt-3 text-xs">
                    <div>Teacher: <strong>{{ $tr?->class_teacher ?? '—' }}</strong></div>
                    <div>Principal: <strong>{{ $tr?->principal ?? '—' }}</strong></div>
                </div>
            </div>
        </td>
    </tr>
</tbody>
@endforeach
        </tbody>
    </table>
</div>
@elseif($classId)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-400 text-sm">
    No results found. Make sure marks have been entered.
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center text-gray-400 text-sm">
    Select a class to view results.
</div>
@endif
@endsection
