@extends('layouts.teacher')
@section('title','My Students')
@section('page-title','My Students')
@section('content')
<div class="space-y-4">
    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Filter by Class</label>
                <select name="class_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All My Classes</option>
                    @foreach($mySubjects->unique('class_id') as $cs)
                    <option value="{{ $cs->class_id }}" {{ request('class_id') == $cs->class_id ? 'selected' : '' }}>
                        {{ $cs->schoolClass->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-white rounded-lg text-sm" style="background:var(--sidebar-bg)">
                Filter
            </button>
            @if(request('class_id'))
            <a href="{{ route('teacher.my-students') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Students</h2>
            <span class="text-xs text-gray-400">{{ $students->count() }} student(s)</span>
        </div>
        @if($students->isEmpty())
        <div class="px-5 py-12 text-center text-gray-400 text-sm">No students found.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">#</th>
                        <th class="px-5 py-3 text-left">Student</th>
                        <th class="px-5 py-3 text-left">Admission No</th>
                        <th class="px-5 py-3 text-left">Class</th>
                        <th class="px-5 py-3 text-left">Section</th>
                        <th class="px-5 py-3 text-left">Gender</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($students as $i => $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $student->full_name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $student->admission_no }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $student->schoolClass?->name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $student->section?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ ucfirst($student->gender ?? '—') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
