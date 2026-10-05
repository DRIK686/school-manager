@extends('layouts.teacher')
@section('title','My Classes')
@section('page-title','My Classes & Subjects')
@section('content')
<div class="space-y-6">
@if($byClass->isEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-16 text-center text-gray-400">
        No classes assigned yet. Contact admin to assign you to subjects.
    </div>
@else
@foreach($byClass as $classId => $subjects)
@php $class = $subjects->first()->schoolClass; @endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800">{{ $class->name }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.attendance.mark') }}?class_id={{ $classId }}"
               class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
               Mark Attendance
            </a>
            <a href="{{ route('teacher.my-students') }}?class_id={{ $classId }}"
               class="text-xs px-3 py-1.5 rounded-lg text-white" style="background:var(--sidebar-bg)">
               View Students
            </a>
        </div>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($subjects as $cs)
        <div class="px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                     style="background:var(--sidebar-bg)">
                    {{ strtoupper(substr($cs->subject->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $cs->subject->name }}</p>
                    <p class="text-xs text-gray-400">{{ ucfirst($cs->subject->type) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endif
</div>
@endsection
