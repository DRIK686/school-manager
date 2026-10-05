@extends('layouts.teacher')
@section('title', 'Mark Attendance')
@section('content')

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="w-44">
            <label class="block text-xs font-medium text-gray-600 mb-1">Date <span class="text-red-500">*</span></label>
            <input type="date" name="date" value="{{ $date }}" required
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        </div>
        <div class="w-40">
            <label class="block text-xs font-medium text-gray-600 mb-1">Class <span class="text-red-500">*</span></label>
            <select name="class_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">Select class...</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        @if($sections->count())
        <div class="w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
            <select name="section_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>Section {{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="btn-primary px-5 py-2 text-sm">Load Students</button>
        <a href="{{ route('admin.attendance.report') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">
            Monthly Report
        </a>
    </form>
</div>

@if($students->count())
<form method="POST" action="{{ route('admin.attendance.save') }}">
@csrf
<input type="hidden" name="class_id" value="{{ $classId }}">
<input type="hidden" name="section_id" value="{{ $sectionId }}">
<input type="hidden" name="date" value="{{ $date }}">

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">
                Attendance — {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $students->count() }} students</p>
        </div>
        {{-- Bulk actions --}}
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">Mark all as:</span>
            <button type="button" onclick="markAll('present')"
                class="text-xs px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition font-medium">Present</button>
            <button type="button" onclick="markAll('absent')"
                class="text-xs px-3 py-1.5 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition font-medium">Absent</button>
            <button type="button" onclick="markAll('holiday')"
                class="text-xs px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-medium">Holiday</button>
        </div>
    </div>

    {{-- Student List --}}
    <div class="divide-y divide-gray-50">
        @foreach($students as $i => $student)
        @php $att = $existing->get($student->id); @endphp
        <div class="flex items-center gap-4 px-6 py-3 hover:bg-gray-50 transition">
            {{-- Student info --}}
            <div class="w-8 text-xs text-gray-400 font-medium text-center">{{ $i + 1 }}</div>
            <div class="flex items-center gap-3 flex-1 min-w-0">
                @if($student->profile_photo)
                    <img src="{{ asset('storage/'.$student->profile_photo) }}"
                        class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                        style="background:{{ $student->gender === 'male' ? 'var(--sidebar-bg)' : '#e11d48' }}">
                        {{ strtoupper(substr($student->first_name,0,1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $student->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $student->admission_no }}</p>
                </div>
            </div>

            {{-- Status Radio Buttons --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                @foreach(['present' => ['green','P'], 'absent' => ['red','A'], 'late' => ['yellow','L'], 'holiday' => ['blue','H']] as $status => [$color, $label])
                <label class="cursor-pointer">
                    <input type="radio" name="attendance[{{ $student->id }}]"
                        value="{{ $status }}"
                        class="sr-only peer"
                        {{ ($att?->status ?? 'present') === $status ? 'checked' : '' }}>
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold border-2 transition-all
                        peer-checked:text-white peer-checked:border-transparent
                        @if($color === 'green') border-green-200 text-green-600 peer-checked:bg-green-500
                        @elseif($color === 'red') border-red-200 text-red-500 peer-checked:bg-red-500
                        @elseif($color === 'yellow') border-yellow-200 text-yellow-600 peer-checked:bg-yellow-400
                        @else border-blue-200 text-blue-500 peer-checked:bg-blue-500
                        @endif">
                        {{ $label }}
                    </span>
                </label>
                @endforeach
            </div>

            {{-- Remarks --}}
            <input type="text" name="remarks[{{ $student->id }}]"
                value="{{ $att?->remarks }}"
                placeholder="Remarks..."
                class="w-32 px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 text-gray-600">
        </div>
        @endforeach
    </div>

    {{-- Submit --}}
    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50">
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Present</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Absent</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> Late</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Holiday</span>
        </div>
        <button type="submit" class="btn-primary px-8 py-2.5">
            Save Attendance
        </button>
    </div>
</div>
</form>

@elseif($classId)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center text-gray-400">
    <p class="text-sm">No active students found in this class.</p>
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center text-gray-400">
    <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p class="text-sm">Select a class and date above to mark attendance.</p>
</div>
@endif

<script>
function markAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(r => r.checked = true);
}
</script>
@endsection
