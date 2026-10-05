@extends('layouts.admin')
@section('title','Promotion Roster')
@section('page-title','Review & Promote — ' . $fromClass->name)
@section('content')

<form method="POST" action="{{ route('admin.promotion.process') }}" x-data="{ bulkAction: 'promote', bulkClass: '{{ $nextClass->id ?? '' }}' }">
    @csrf

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-gray-800 text-lg">{{ $fromClass->name }}</h2>
                <p class="text-sm text-gray-500">{{ $students->count() }} student(s) found</p>
            </div>
            <a href="{{ route('admin.promotion.index') }}" class="text-sm text-gray-500 hover:underline">← Back</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Promote To Academic Year</label>
                <select name="to_academic_year_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">Select target year...</option>
                    @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Bulk action bar --}}
        <div class="flex flex-wrap items-end gap-3 bg-gray-50 rounded-lg p-4 border border-gray-100">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulk Action</label>
                <select x-model="bulkAction" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="promote">Promote</option>
                    <option value="repeat">Repeat Class</option>
                    <option value="graduate">Graduate</option>
                    <option value="withdraw">Withdraw</option>
                </select>
            </div>
            <div x-show="bulkAction === 'promote'">
                <label class="block text-xs font-medium text-gray-600 mb-1">Promote To Class</label>
                <select x-model="bulkClass" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">Select class...</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ (isset($nextClass) && $nextClass->id == $class->id) ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button"
                    @click="
                        document.querySelectorAll('[data-action]').forEach(el => el.value = bulkAction);
                        document.querySelectorAll('[data-class]').forEach(el => el.value = bulkClass);
                        document.querySelectorAll('[data-row]').forEach(el => el.dispatchEvent(new Event('refresh')));
                    "
                    class="text-xs px-4 py-2 text-white rounded-lg" style="background:var(--sidebar-bg)">
                Apply to All Students Below
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Student</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Admission No.</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Action</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">New Class</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">New Section</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $student)
                @php $rec = ($recommendations ?? collect())->get($student->id); @endphp
                <tr class="border-b border-gray-50" data-row x-data="{ action: '{{ $rec->recommended_action ?? 'promote' }}', cls: '{{ $rec->recommended_class_id ?? $nextClass->id ?? '' }}' }" @refresh.window="action = document.querySelector('[data-action]').value; cls = document.querySelector('[data-class]').value">
                    <input type="hidden" name="students[{{ $i }}][id]" value="{{ $student->id }}">
                    <td class="px-4 py-3">
                        {{ $student->full_name }}
                        @if($rec)
                        <div class="text-xs text-blue-500 mt-0.5">
                            👤 {{ $rec->teacher->name ?? 'Teacher' }} suggested: {{ ucfirst($rec->recommended_action) }}
                            @if($rec->remarks) — "{{ $rec->remarks }}" @endif
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $student->admission_no }}</td>
                    <td class="px-4 py-3">
                        <select name="students[{{ $i }}][action]" x-model="action" data-action
                                class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                            <option value="promote">Promote</option>
                            <option value="repeat">Repeat Class</option>
                            <option value="graduate">Graduate</option>
                            <option value="withdraw">Withdraw</option>
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <select name="students[{{ $i }}][class_id]" x-model="cls" data-class
                                x-show="action === 'promote'"
                                class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                            <option value="">Select...</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (isset($nextClass) && $nextClass->id == $class->id) ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <span x-show="action !== 'promote'" class="text-xs text-gray-400 italic">—</span>
                    </td>
                    <td class="px-4 py-3">
                        <select name="students[{{ $i }}][section_id]"
                                x-show="action === 'promote'"
                                class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                            <option value="">None</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                        <span x-show="action !== 'promote'" class="text-xs text-gray-400 italic">—</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit"
                onclick="return confirm('This will move {{ $students->count() }} student(s) according to the actions selected. This cannot be undone from the UI. Continue?')"
                class="btn-primary text-sm px-6 py-2.5">
            Confirm & Process Promotion
        </button>
        <a href="{{ route('admin.promotion.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
    </div>
</form>
@endsection
