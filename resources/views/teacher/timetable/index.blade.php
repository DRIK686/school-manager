@extends('layouts.teacher')
@section('title','Timetable')
@section('page-title','Class Timetable')
@section('content')
<div class="space-y-4" x-data="timetableApp()">

    {{-- Toolbar --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">Select class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($sections->count())
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                <select name="section_id" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All Sections</option>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>
                        {{ $sec->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            @if($classId)
            <a href="{{ route('admin.timetable.view', ['class_id' => $classId, 'section_id' => $sectionId]) }}"
               target="_blank"
               class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                🖨 Print View
            </a>
            @endif
        </form>
    </div>

    @if(!$classId)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-16 text-center text-gray-400">
        Select a class above to view or edit its timetable.
    </div>
    @else

    @if(! $canEdit)
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        View only. Only the class teacher or an administrator can change this timetable.
    </div>
    @endif
    {{-- Grid --}}
    <form method="POST" action="{{ route('admin.timetable.save') }}">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $year?->id }}">
        <input type="hidden" name="class_id"         value="{{ $classId }}">
        <input type="hidden" name="section_id"       value="{{ $sectionId }}">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">
                    {{ $classes->firstWhere('id', $classId)?->name }} Timetable
                    — {{ $year?->name }}
                </h2>
                @if($canEdit)
                <button type="submit"
                        class="px-4 py-2 text-white rounded-lg text-sm font-medium"
                        style="background:var(--sidebar-bg)">
                    Save Timetable
                </button>
                @endif
            </div>

            <table class="w-full text-xs border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 border border-gray-100 w-24">
                            Time
                        </th>
                        @foreach($days as $dayNo => $dayName)
                        <th class="px-3 py-2 text-center font-semibold text-gray-600 border border-gray-100">
                            {{ $dayName }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($slots as $slot)
                    <tr class="{{ $slot->is_break ? 'bg-amber-50' : 'hover:bg-gray-50' }}">
                        {{-- Time label --}}
                        <td class="px-3 py-2 border border-gray-100 align-top">
                            <div class="font-semibold {{ $slot->is_break ? 'text-amber-700' : 'text-gray-700' }}">
                                {{ $slot->label }}
                            </div>
                            <div class="text-gray-400 text-xs mt-0.5">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                        </td>

                        @foreach($days as $dayNo => $dayName)
                        @php $entry = $grid[$dayNo][$slot->id] ?? null; @endphp
                        <td class="border border-gray-100 p-1 align-top min-w-[130px]">
                            @if($slot->is_break)
                                {{-- Break/Opening: just custom label --}}
                                <input type="text"
                                       name="entries[{{ $dayNo }}][{{ $slot->id }}][custom_label]"
                                       value="{{ $entry?->custom_label ?? $slot->label }}"
                                       class="w-full px-2 py-1 text-xs rounded border border-amber-200 bg-amber-50 text-amber-800 focus:outline-none focus:ring-1"
                                       placeholder="{{ $slot->label }}" {{ $canEdit ? '' : 'disabled' }}>
                            @else
                                {{-- Subject --}}
                                <select name="entries[{{ $dayNo }}][{{ $slot->id }}][subject_id]"
                                        class="w-full px-1 py-1 text-xs rounded border border-gray-200 focus:outline-none focus:ring-1 mb-1" {{ $canEdit ? '' : 'disabled' }}>
                                    <option value="">—</option>
                                    @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}"
                                            {{ $entry?->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                    @endforeach
                                </select>
                                {{-- Teacher --}}
                                <select name="entries[{{ $dayNo }}][{{ $slot->id }}][teacher_id]"
                                        class="w-full px-1 py-1 text-xs rounded border border-gray-200 focus:outline-none focus:ring-1 text-gray-500" {{ $canEdit ? '' : 'disabled' }}>
                                    <option value="">No teacher</option>
                                    @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                            {{ $entry?->teacher_id == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-5 py-3 border-t border-gray-100 flex justify-end {{ $canEdit ? '' : 'hidden' }}">
                <button type="submit"
                        class="px-5 py-2 text-white rounded-lg text-sm font-medium"
                        style="background:var(--sidebar-bg)">
                    Save Timetable
                </button>
            </div>
        </div>
    </form>
    @endif
</div>

<script>
function timetableApp() { return {}; }
</script>
@endsection
