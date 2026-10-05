@extends('layouts.student')
@section('title','Results')
@section('page-title','My Results')
@section('content')
<div class="space-y-4">
    {{-- Academic year selector --}}
    @if($academicYears->count() > 1)
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

    @if($examTypes->isEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-16 text-center text-gray-400 text-sm">
        No results published yet for {{ $year?->name ?? 'this year' }}.
    </div>
    @else

    {{-- Exam selector + download button --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-wrap gap-3 items-end justify-between">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="academic_year_id" value="{{ $year?->id }}">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Exam</label>
                    <select name="exam_id" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                        @foreach($examTypes as $exam)
                        <option value="{{ $exam->id }}" {{ $examId==$exam->id?'selected':'' }}>{{ $exam->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            @if($examId)
            <a href="{{ route('student.report-card', $examId) }}"
               class="flex items-center gap-2 px-4 py-2 text-white rounded-lg text-sm font-medium"
               style="background:var(--sidebar-bg)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Report Card
            </a>
            @endif
        </div>
    </div>

    @if($marks->isNotEmpty())
    {{-- Summary cards --}}
    @php
        $totalScore  = $marks->where('is_absent', false)->sum('marks_obtained');
        $totalMax    = $marks->count() * 100;
        $avg         = $marks->count() > 0 ? round($totalScore / max(1, $marks->count()), 1) : 0;
    @endphp
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400">Total Score</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--sidebar-bg)">
                {{ number_format($totalScore, 1) }}/{{ $totalMax }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400">Average</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--sidebar-bg)">{{ $avg }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400">Subjects</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--sidebar-bg)">{{ $marks->count() }}</p>
        </div>
    </div>

    {{-- Marks table with SBA + Exam --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Subject</th>
                        <th class="px-3 py-3 text-center bg-blue-50 text-blue-600">SBA (50%)</th>
                        <th class="px-3 py-3 text-center bg-green-50 text-green-600">Exam (50%)</th>
                        <th class="px-3 py-3 text-center">Total</th>
                        <th class="px-3 py-3 text-center">Class Avg</th>
                        <th class="px-3 py-3 text-center">Grade</th>
                        <th class="px-3 py-3 text-center">Remark</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($marks as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">
                            {{ $m->examSchedule?->subject?->name }}
                        </td>
                        <td class="px-3 py-3 text-center bg-blue-50/30">
                            @if($m->is_absent)
                                <span class="text-red-400 text-xs">Absent</span>
                            @else
                                {{ $m->sba_score !== null ? number_format($m->sba_score, 1) : '—' }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center bg-green-50/30">
                            @if($m->is_absent)
                                <span class="text-red-400 text-xs">Absent</span>
                            @else
                                {{ $m->exam_score !== null ? number_format($m->exam_score, 1) : '—' }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center font-bold text-gray-800">
                            @if($m->is_absent)
                                <span class="text-red-500 text-xs">Absent</span>
                            @else
                                {{ $m->marks_obtained !== null ? number_format($m->marks_obtained, 1) : '—' }}
                                / {{ $m->examSchedule?->max_marks ?? 100 }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-gray-500 text-xs">
                            {{ $m->class_average ? number_format($m->class_average, 1) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-bold text-lg {{ ($m->grade === 'F9') ? 'text-red-500' : '' }}"
                                  style="{{ ($m->grade !== 'F9') ? 'color:var(--sidebar-bg)' : '' }}">
                                {{ $m->is_absent ? '—' : ($m->grade ?? '—') }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if(!$m->is_absent && $m->remarks)
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $m->remarks === 'Excellent' ? 'bg-green-50 text-green-700' :
                                   ($m->remarks === 'Fail' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-700') }}">
                                {{ $m->remarks }}
                            </span>
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-100">
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-700">Total</td>
                        <td class="px-3 py-3 text-center font-semibold text-blue-600 bg-blue-50/30">
                            {{ number_format($marks->where('is_absent',false)->sum('sba_score'), 1) }}
                        </td>
                        <td class="px-3 py-3 text-center font-semibold text-green-600 bg-green-50/30">
                            {{ number_format($marks->where('is_absent',false)->sum('exam_score'), 1) }}
                        </td>
                        <td class="px-3 py-3 text-center font-bold" style="color:var(--sidebar-bg)">
                            {{ number_format($totalScore, 1) }} / {{ $totalMax }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-12 text-center text-gray-400 text-sm">
        No marks recorded for this exam yet.
    </div>
    @endif
    @endif
</div>
@endsection
