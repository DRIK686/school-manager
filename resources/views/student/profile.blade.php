@extends('layouts.student')
@section('title','My Profile')
@section('page-title','My Profile')
@section('content')

<div class="max-w-3xl space-y-4">

    {{-- Header card --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-5">
            @if($student->profile_photo)
            <img src="{{ asset('storage/' . $student->profile_photo) }}"
                 class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md">
            @else
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-md"
                 style="background:var(--sidebar-bg)">
                {{ strtoupper(substr($student->first_name,0,1)) }}
            </div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $student->full_name }}</h2>
                <p class="text-sm text-gray-500">{{ $student->schoolClass?->name }}{{ $student->section ? ' / '.$student->section->name : '' }}</p>
                <p class="text-sm text-gray-400 font-mono">{{ $student->admission_no }}</p>
            </div>
        </div>
    </div>

    {{-- Personal information --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-5">Personal Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
            <div>
                <p class="text-xs text-gray-400 mb-1">Gender</p>
                <p class="text-gray-800">{{ $student->gender ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Date of Birth</p>
                <p class="text-gray-800">{{ $student->dob?->format('d M Y') ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Blood Group</p>
                <p class="text-gray-800">{{ $student->blood_group ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Nationality</p>
                <p class="text-gray-800">{{ $student->nationality ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Phone</p>
                <p class="text-gray-800">{{ $student->phone ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Admission Date</p>
                <p class="text-gray-800">{{ $student->admission_date?->format('d M Y') ?: '—' }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs text-gray-400 mb-1">Address</p>
                <p class="text-gray-800">{{ $student->address ?: '—' }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-6 pt-4 border-t border-gray-100">
            This information is managed by the school. Contact the school office if any details need to be corrected.
        </p>
    </div>

    {{-- Account --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Account</h3>
        <a href="{{ route('student.change-pin') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg"
           style="background:var(--sidebar-bg)">
            Change PIN
        </a>
    </div>

</div>
@endsection
