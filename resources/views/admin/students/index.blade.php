@extends('layouts.admin')
@section('title', 'Students')
@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">All Students</h2>
        <p class="text-sm text-gray-500">{{ $students->total() }} student(s) found</p>
    </div>
    @if(auth()->user()->hasAnyRole(['super_admin','admin','receptionist']))
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.students.import.form') }}"
           class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Import Students
        </a>
        <a href="{{ route('admin.students.create') }}" class="btn-primary px-5 py-2.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Admit Student
        </a>
    </div>
    @endif
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or admission no..."
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        </div>
        <div class="w-40">
            <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
            <select name="class_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">Gender</label>
            <select name="gender" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">All</option>
                <option value="male" {{ request('gender')=='male'?'selected':'' }}>Male</option>
                <option value="female" {{ request('gender')=='female'?'selected':'' }}>Female</option>
            </select>
        </div>
        <div class="w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">All</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn-primary px-4 py-2 text-sm">Filter</button>
        @if(request()->hasAny(['search','class_id','section_id','gender','status']))
            <a href="{{ route('admin.students.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @if($students->count())
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Student</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Admission No</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Class</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Gender</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="px-6 py-3">
                    <div class="flex items-center gap-3">
                        @if($student->profile_photo)
                            <img src="{{ asset('storage/'.$student->profile_photo) }}"
                                class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                style="background:{{ $student->gender === 'male' ? 'var(--sidebar-bg)' : '#e11d48' }}">
                                {{ strtoupper(substr($student->first_name,0,1)) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-800">{{ $student->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $student->dob?->format('d M Y') ?? 'DOB not set' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ $student->admission_no }}</span>
                </td>
                <td class="px-4 py-3 text-gray-600">
                    {{ $student->schoolClass?->name ?? '—' }}
                    @if($student->section) <span class="text-gray-400">/ {{ $student->section->name }}</span> @endif
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $student->gender === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                        {{ ucfirst($student->gender) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @php
                        $badgeColors = [
                            'Active'    => 'bg-green-100 text-green-700',
                            'Graduated' => 'bg-blue-100 text-blue-700',
                            'Withdrawn' => 'bg-amber-100 text-amber-700',
                            'Inactive'  => 'bg-gray-100 text-gray-500',
                        ];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColors[$student->status_label] ?? $badgeColors['Inactive'] }}">
                        {{ $student->status_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.students.show', $student) }}"
                        class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $students->links() }}
    </div>
    @else
    <div class="py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <p class="text-gray-400 text-sm">No students found.</p>
        <a href="{{ route('admin.students.create') }}" class="mt-3 inline-block btn-primary px-5 py-2 text-sm">Admit First Student</a>
    </div>
    @endif
</div>
@endsection
