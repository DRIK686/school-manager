@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

{{-- Welcome banner --}}
<div class="rounded-2xl p-6 text-white mb-6" style="background:var(--sidebar-bg)">
    <h2 class="text-xl font-bold">Welcome back, {{ auth()->user()->name }} 👋</h2>
    <p class="text-white/60 text-sm mt-1">
        {{ $school->school_name }} &mdash; {{ now()->format('l, d F Y') }}
        @if($year) · {{ $year->name }} @endif
    </p>
</div>

{{-- Academic year transition reminder --}}
@if($yearEndingSoon ?? false)
<div class="rounded-xl p-4 mb-6 border flex items-center justify-between gap-4
     {{ $daysToYearEnd < 0 ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200' }}">
    <div class="flex items-center gap-3">
        <span class="text-2xl">{{ $daysToYearEnd < 0 ? '⚠️' : '📅' }}</span>
        <div>
            <p class="font-semibold text-sm {{ $daysToYearEnd < 0 ? 'text-red-700' : 'text-yellow-700' }}">
                @if($daysToYearEnd < 0)
                    "{{ $year->name }}" ended {{ abs($daysToYearEnd) }} day(s) ago.
                @else
                    "{{ $year->name }}" ends in {{ $daysToYearEnd }} day(s).
                @endif
            </p>
            <p class="text-xs {{ $daysToYearEnd < 0 ? 'text-red-600' : 'text-yellow-600' }} mt-0.5">
                Review and promote students, then set the new academic year as current when ready.
            </p>
        </div>
    </div>
    <a href="{{ route('admin.promotion.index') }}"
       class="text-xs font-semibold px-4 py-2 rounded-lg whitespace-nowrap
              {{ $daysToYearEnd < 0 ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-yellow-500 text-white hover:bg-yellow-600' }}">
        Go to Promotion →
    </a>
</div>
@endif

{{-- Stat cards --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Total Students</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_students'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Active enrolments</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Teachers</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_teachers'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['total_staff'] }} total staff</p>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Fees Collected</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                    {{ $school->currency_symbol }}{{ number_format($stats['fees_collected'], 2) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $year?->name }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Today's Attendance</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['present_today'] }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $stats['absent_today'] }} absent today
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.students.create') }}"
           class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-100 transition text-center group">
            <svg class="w-7 h-7 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            <span class="text-xs font-semibold text-gray-600">Add Student</span>
        </a>
        <a href="{{ route('admin.fees.collect') }}"
           class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-100 transition text-center group">
            <svg class="w-7 h-7 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-xs font-semibold text-gray-600">Collect Fees</span>
        </a>
        <a href="{{ route('admin.attendance.mark') }}"
           class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-100 transition text-center group">
            <svg class="w-7 h-7 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span class="text-xs font-semibold text-gray-600">Attendance</span>
        </a>
        <a href="{{ route('admin.exams.index') }}"
           class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-100 transition text-center group">
            <svg class="w-7 h-7 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-xs font-semibold text-gray-600">Exam Results</span>
        </a>
    </div>
</div>

{{-- Bottom row: Recent notices + class summary --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Recent Notices --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Recent Notices</h3>
            <a href="{{ route('admin.notices.index') }}" class="text-xs font-medium" style="color:var(--sidebar-bg)">
                Manage →
            </a>
        </div>
        @php
            $notices = \App\Models\Notice::active()->latest()->take(4)->get();
        @endphp
        @forelse($notices as $notice)
        <div class="px-5 py-3 border-b border-gray-50 last:border-0">
            <p class="text-sm font-medium text-gray-800">{{ $notice->title }}</p>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ ucfirst($notice->audience) }} · {{ $notice->created_at->diffForHumans() }}
            </p>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400 text-sm">No notices posted yet.</div>
        @endforelse
    </div>

    {{-- Class summary --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Classes Overview</h3>
            <a href="{{ route('admin.classes.index') }}" class="text-xs font-medium" style="color:var(--sidebar-bg)">
                Manage →
            </a>
        </div>
        @php
            $classes = \App\Models\SchoolClass::withCount(['students' => fn($q) => $q->where('is_active', true)])
                ->orderBy('numeric_order')->take(6)->get();
        @endphp
        @forelse($classes as $class)
        <div class="px-5 py-3 border-b border-gray-50 last:border-0 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-800">{{ $class->name }}</p>
            <span class="text-xs font-semibold px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">
                {{ $class->students_count }} student{{ $class->students_count != 1 ? 's' : '' }}
            </span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400 text-sm">No classes set up yet.</div>
        @endforelse
    </div>

</div>

@endsection
