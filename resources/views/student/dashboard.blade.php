@extends('layouts.student')
@section('title','Dashboard')
@section('page-title','My Dashboard')
@section('content')
<div class="space-y-6">

{{-- Welcome --}}
<div class="rounded-xl p-5 text-white" style="background:var(--sidebar-bg)">
    <p class="text-white/70 text-sm">Welcome back,</p>
    <h2 class="text-xl font-bold mt-0.5">{{ $student->full_name }}</h2>
    <p class="text-white/70 text-xs mt-1">
        {{ $student->schoolClass?->name }} @if($student->section) · {{ $student->section->name }} @endif
        · {{ $student->admission_no }}
    </p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">Fees Paid</p>
        <p class="text-2xl font-bold mt-1" style="color:var(--sidebar-bg)">GH₵{{ number_format($totalFeesPaid,2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">Present Days</p>
        <p class="text-2xl font-bold mt-1 text-green-600">{{ $attendanceSummary['present'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">Absent Days</p>
        <p class="text-2xl font-bold mt-1 text-red-500">{{ $attendanceSummary['absent'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <p class="text-xs text-gray-400 uppercase font-semibold">Pending HW</p>
        <p class="text-2xl font-bold mt-1 text-amber-500">{{ $homework->count() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Notices --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Latest Notices</h2>
            <a href="{{ route('student.notices') }}" class="text-xs" style="color:var(--sidebar-bg)">All →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($notices as $notice)
            <div class="px-5 py-3">
                <p class="text-sm font-medium text-gray-800">{{ $notice->title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $notice->created_at->format('d M Y') }}</p>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No notices.</div>
            @endforelse
        </div>
    </div>

    {{-- Homework --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Upcoming Homework</h2>
            <a href="{{ route('student.homework') }}" class="text-xs" style="color:var(--sidebar-bg)">All →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($homework as $hw)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $hw->title }}</p>
                    <p class="text-xs text-gray-400">{{ $hw->subject->name }} · Due {{ $hw->due_date->format('d M') }}</p>
                </div>
                @if($hw->due_date->isToday())
                <span class="text-xs px-2 py-0.5 bg-red-50 text-red-600 rounded-full">Today!</span>
                @endif
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No pending homework.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Exam Results --}}
@if($recentResults->count())
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800">Published Results</h2>
        <a href="{{ route('student.results') }}" class="text-xs" style="color:var(--sidebar-bg)">View →</a>
    </div>
    <div class="px-5 py-3 flex flex-wrap gap-2">
        @foreach($recentResults as $exam)
        <a href="{{ route('student.results', ['exam_id'=>$exam->id]) }}"
           class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
            {{ $exam->name }}
        </a>
        @endforeach
    </div>
</div>
@endif

</div>
@endsection
