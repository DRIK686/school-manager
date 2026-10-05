@extends('layouts.admin')
@section('title', $exam->name . ' — Schedule')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.exams.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Exams
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add Schedule Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Add to Schedule</h3>
        <form method="POST" action="{{ route('admin.exams.schedule.store', $exam) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class <span class="text-red-500">*</span></label>
                <select name="class_id" id="sched-class-select" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    <option value="">Select class...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Subject <span class="text-red-500">*</span></label>
                <select name="subject_id" id="sched-subject-select" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    <option value="">Select a class first</option>
                </select>
                @error('subject_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <script>
                const schedClassSubjects = @json($classSubjectsMap);
                function schedRebuildSubjects() {
                    const classId = document.getElementById('sched-class-select').value;
                    const subjectSelect = document.getElementById('sched-subject-select');
                    const subjects = schedClassSubjects[classId] || [];
                    if (!classId) {
                        subjectSelect.innerHTML = '<option value="">Select a class first</option>';
                        return;
                    }
                    if (subjects.length === 0) {
                        subjectSelect.innerHTML = '<option value="">No subjects assigned to this class</option>';
                        return;
                    }
                    subjectSelect.innerHTML = '<option value="">Select subject...</option>';
                    subjects.forEach(function(s) {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        subjectSelect.appendChild(opt);
                    });
                }
                document.getElementById('sched-class-select').addEventListener('change', schedRebuildSubjects);
            </script>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Exam Date</label>
                <input type="date" name="exam_date"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Start Time</label>
                    <input type="time" name="start_time"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">End Time</label>
                    <input type="time" name="end_time"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Room</label>
                <input type="text" name="room" placeholder="e.g. Hall A"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Max Marks</label>
                    <input type="number" name="max_marks" value="100" min="1"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pass Marks</label>
                    <input type="number" name="passing_marks" value="50" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
            <button type="submit" class="btn-primary w-full py-2">Add to Schedule</button>
        </form>
    </div>

    {{-- Schedule List --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">{{ $exam->name }} — Schedule ({{ $schedules->count() }} entries)</h3>
        </div>
        @forelse($schedules as $schedule)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-2 h-10 rounded-full flex-shrink-0" style="background:{{ $schedule->subject->color }}"></div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $schedule->subject->name }}
                        <span class="text-xs text-gray-400 font-normal ml-1">{{ $schedule->schoolClass->name }}</span>
                    </p>
                    <p class="text-xs text-gray-400">
                        @if($schedule->exam_date) {{ $schedule->exam_date->format('d M Y') }} @endif
                        @if($schedule->start_time) • {{ $schedule->start_time }} @endif
                        @if($schedule->room) • {{ $schedule->room }} @endif
                        • Max: {{ $schedule->max_marks }} | Pass: {{ $schedule->passing_marks }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.exams.schedule.destroy', [$exam, $schedule]) }}"
                onsubmit="return confirm('Remove this schedule entry?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
            </form>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-gray-400 text-sm">No schedule entries yet.</div>
        @endforelse
    </div>
</div>
@endsection
