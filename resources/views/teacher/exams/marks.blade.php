@extends('layouts.teacher')
@section('title', $exam->name . ' — Enter Marks')
@section('content')

<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('admin.exams.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Exams
    </a>
</div>

{{-- Class Filter --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex gap-3 items-end flex-wrap">
        <div class="w-44">
            <label class="block text-xs font-medium text-gray-600 mb-1">Select Class</label>
            <select name="class_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">Select class...</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary px-5 py-2 text-sm">Load</button>
    </form>
</div>

@if($classId && $schedules->count() && $students->count())
<form method="POST" action="{{ route('admin.exams.marks.save', $exam) }}">
@csrf
<input type="hidden" name="class_id" value="{{ $classId }}">

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800">{{ $exam->name }} — Marks Entry</h3>
        <div class="flex items-center gap-4">
            <span class="text-xs text-gray-400">{{ $students->count() }} students · {{ $schedules->count() }} subjects</span>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-100 inline-block"></span> SBA (50%)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 inline-block"></span> Exam (50%)</span>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 sticky left-0 bg-gray-50 min-w-44" rowspan="2">Student</th>
                    @foreach($schedules as $schedule)
                    <th class="px-2 py-2 text-center font-semibold text-gray-600 border-l border-gray-100" colspan="3">
                        <div style="color:{{ $schedule->subject->color ?? 'var(--sidebar-bg)' }}">{{ $schedule->subject->name }}</div>
                        <div class="text-gray-400 font-normal text-xs">Max: {{ $schedule->max_marks }}</div>
                    </th>
                    @endforeach
                </tr>
                <tr class="bg-gray-50 border-b border-gray-200">
                    @foreach($schedules as $schedule)
                    <th class="px-1 py-1.5 text-center text-xs font-medium text-blue-600 bg-blue-50 border-l border-gray-100 w-16">SBA</th>
                    <th class="px-1 py-1.5 text-center text-xs font-medium text-green-600 bg-green-50 w-16">Exam</th>
                    <th class="px-1 py-1.5 text-center text-xs font-medium text-gray-500 bg-gray-50 w-16">Total</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{}">
                    <td class="px-4 py-2 sticky left-0 bg-white border-r border-gray-100">
                        <p class="font-medium text-gray-800">{{ $student->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $student->admission_no }}</p>
                    </td>
                    @foreach($schedules as $schedule)
                    @php $existing = $existingMarks->get($student->id . '-' . $schedule->id); @endphp
                    <td class="px-1 py-1.5 text-center bg-blue-50/30 border-l border-gray-100">
                        <input type="number"
                            name="marks[{{ $student->id }}][{{ $schedule->id }}][sba]"
                            value="{{ $existing?->is_absent ? '' : $existing?->sba_score }}"
                            min="0" max="{{ $schedule->max_marks / 2 }}" step="0.5"
                            {{ $existing?->is_absent ? 'disabled' : '' }}
                            placeholder="—"
                            class="sba-input w-14 px-1 py-1 border border-gray-200 rounded text-center text-xs focus:outline-none focus:ring-1 focus:border-blue-400 disabled:bg-gray-100"
                            data-student="{{ $student->id }}" data-schedule="{{ $schedule->id }}">
                    </td>
                    <td class="px-1 py-1.5 text-center bg-green-50/30">
                        <input type="number"
                            name="marks[{{ $student->id }}][{{ $schedule->id }}][exam]"
                            value="{{ $existing?->is_absent ? '' : $existing?->exam_score }}"
                            min="0" max="{{ $schedule->max_marks / 2 }}" step="0.5"
                            {{ $existing?->is_absent ? 'disabled' : '' }}
                            placeholder="—"
                            class="exam-input w-14 px-1 py-1 border border-gray-200 rounded text-center text-xs focus:outline-none focus:ring-1 focus:border-green-400 disabled:bg-gray-100"
                            data-student="{{ $student->id }}" data-schedule="{{ $schedule->id }}">
                    </td>
                    <td class="px-1 py-1.5 text-center">
                        <div class="flex flex-col items-center gap-0.5">
                            <span class="total-display w-14 text-center text-xs font-bold py-1 rounded bg-gray-100 text-gray-700"
                                  id="total-{{ $student->id }}-{{ $schedule->id }}">
                                {{ $existing && !$existing->is_absent ? ($existing->marks_obtained ?? '—') : '—' }}
                            </span>
                            @if($existing && !$existing->is_absent && $existing->grade)
                            <span class="text-xs font-bold" style="color:var(--sidebar-bg)">{{ $existing->grade }}</span>
                            @endif
                            <label class="flex items-center gap-1 text-xs text-gray-400 cursor-pointer mt-0.5">
                                <input type="checkbox"
                                    name="absent[{{ $student->id }}][{{ $schedule->id }}]"
                                    value="1"
                                    {{ $existing?->is_absent ? 'checked' : '' }}
                                    onchange="toggleAbsent(this, {{ $student->id }}, {{ $schedule->id }})"
                                    class="rounded border-gray-300 w-3 h-3">
                                <span>Abs</span>
                            </label>
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100 flex justify-end bg-gray-50">
        <button type="submit" class="btn-primary px-8 py-2.5">Save All Marks</button>
    </div>
</div>
</form>

<script>
function toggleAbsent(checkbox, studentId, scheduleId) {
    const row = checkbox.closest('tr');
    const sba  = row.querySelector(`input[name="marks[${studentId}][${scheduleId}][sba]"]`);
    const exam = row.querySelector(`input[name="marks[${studentId}][${scheduleId}][exam]"]`);
    const total = document.getElementById(`total-${studentId}-${scheduleId}`);
    sba.disabled  = checkbox.checked;
    exam.disabled = checkbox.checked;
    if (checkbox.checked) { sba.value = ''; exam.value = ''; total.textContent = 'ABS'; }
    else { total.textContent = '—'; }
}

// Auto-calculate total when SBA or Exam changes
document.addEventListener('input', function(e) {
    if (!e.target.classList.contains('sba-input') && !e.target.classList.contains('exam-input')) return;
    const studentId  = e.target.dataset.student;
    const scheduleId = e.target.dataset.schedule;
    const row = e.target.closest('tr');
    const sba  = parseFloat(row.querySelector(`input[name="marks[${studentId}][${scheduleId}][sba]"]`)?.value) || 0;
    const exam = parseFloat(row.querySelector(`input[name="marks[${studentId}][${scheduleId}][exam]"]`)?.value) || 0;
    const total = document.getElementById(`total-${studentId}-${scheduleId}`);
    if (total) total.textContent = (sba + exam).toFixed(1);
});
</script>

@elseif($classId)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-400 text-sm">
    @if(!$schedules->count())
        No exam schedule found for this class. Please ask admin to add an exam schedule.
    @else
        No active students found in this class.
    @endif
</div>
@endif
@endsection
