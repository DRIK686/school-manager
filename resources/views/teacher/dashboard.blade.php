@extends('layouts.teacher')
@section('title','Dashboard')
@section('page-title','My Dashboard')

@section('content')
<div class="space-y-6">

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">My Subjects</p>
        <p class="text-3xl font-bold mt-1" style="color:var(--sidebar-bg)">{{ $mySubjects->count() }}</p>
        <p class="text-xs text-gray-400 mt-1">across all classes</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">My Students</p>
        <p class="text-3xl font-bold mt-1" style="color:var(--sidebar-bg)">{{ $totalStudents }}</p>
        <p class="text-xs text-gray-400 mt-1">active students</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">Today's Attendance</p>
        <p class="text-3xl font-bold mt-1" style="color:var(--sidebar-bg)">{{ $todayAttendance }}</p>
        <p class="text-xs text-gray-400 mt-1">records today</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">Draft Plans</p>
        <p class="text-3xl font-bold mt-1 text-amber-500">{{ $pendingPlans }}</p>
        <p class="text-xs text-gray-400 mt-1">awaiting submission</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- My Subjects --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">My Assigned Subjects</h2>
            <a href="{{ route('teacher.my-classes') }}" class="text-xs" style="color:var(--sidebar-bg)">View all →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($mySubjects as $cs)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $cs->subject->name }}</p>
                    <p class="text-xs text-gray-400">{{ $cs->schoolClass->name }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.attendance.mark') }}?class_id={{ $cs->class_id }}"
                       class="text-xs px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                       Attendance
                    </a>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                No subjects assigned yet. Ask admin to assign you to classes.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions + Upcoming Exams --}}
    <div class="space-y-4">
        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('teacher.lesson-plans.create') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-dashed border-gray-200 hover:border-gray-300 text-center text-sm text-gray-600 hover:text-gray-800 transition">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Lesson Plan
                </a>
                <a href="{{ route('admin.attendance.mark') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-dashed border-gray-200 hover:border-gray-300 text-center text-sm text-gray-600 hover:text-gray-800 transition">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Mark Attendance
                </a>
                <a href="{{ route('admin.exams.index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-dashed border-gray-200 hover:border-gray-300 text-center text-sm text-gray-600 hover:text-gray-800 transition">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Enter Marks
                </a>
                <a href="{{ route('teacher.my-students') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-dashed border-gray-200 hover:border-gray-300 text-center text-sm text-gray-600 hover:text-gray-800 transition">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    My Students
                </a>
            </div>
        </div>

        {{-- Upcoming Exams --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Upcoming Exams</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($upcomingExams as $exam)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $exam->name }}</p>
                        <p class="text-xs text-gray-400">{{ $exam->academicYear?->name }}</p>
                    </div>
                    <a href="{{ route('admin.exams.marks', $exam) }}"
                       class="text-xs px-3 py-1 rounded-lg text-white"
                       style="background:var(--sidebar-bg)">Enter Marks</a>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-gray-400 text-sm">No upcoming exams.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div>
@endsection
