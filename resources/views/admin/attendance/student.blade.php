@extends('layouts.admin')
@section('title', $student->full_name . ' — Attendance')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.attendance.mark') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Attendance
    </a>
</div>

{{-- Student Header --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
            style="background:var(--sidebar-bg)">
            {{ strtoupper(substr($student->first_name,0,1)) }}
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-800">{{ $student->full_name }}</h2>
            <p class="text-sm text-gray-500">{{ $student->admission_no }} • {{ $student->schoolClass?->name }}</p>
        </div>
        <form method="GET" class="ml-auto flex gap-2 items-end">
            <select name="month" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->format('F') }}</option>
                @endforeach
            </select>
            <select name="year" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @foreach([date('Y')-1, date('Y')] as $y)
                    <option value="{{ $y }}" {{ $yr == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary px-4 py-2 text-sm">View</button>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Present', $present, 'green'],
        ['Absent',  $absent,  'red'],
        ['Late',    $late,    'yellow'],
        ['Attendance %', $pct.'%', $pct >= 75 ? 'green' : 'red'],
    ] as [$label,$value,$color])
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">{{ $label }}</p>
        <p class="text-2xl font-bold {{ $color === 'green' ? 'text-green-600' : ($color === 'red' ? 'text-red-500' : 'text-yellow-500') }}">
            {{ $value }}
        </p>
    </div>
    @endforeach
</div>

{{-- Daily Records --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-800">
            Daily Records — {{ \Carbon\Carbon::createFromDate($yr, $month, 1)->format('F') }} {{ $yr }}
        </h3>
    </div>
    @if($records->count())
    <div class="divide-y divide-gray-50">
        @foreach($records as $rec)
        <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0
                    {{ $rec->status === 'present' ? 'bg-green-500' :
                       ($rec->status === 'absent' ? 'bg-red-500' :
                       ($rec->status === 'late' ? 'bg-yellow-400' : 'bg-blue-400')) }}">
                    {{ strtoupper(substr($rec->status,0,1)) }}
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $rec->date->format('l, d F Y') }}
                        @if($rec->taken_at)
                            <span class="text-xs text-gray-400 font-normal">— marked at {{ $rec->taken_at->format('g:i A') }}</span>
                        @endif
                    </p>
                    @if($rec->remarks)<p class="text-xs text-gray-400">{{ $rec->remarks }}</p>@endif
                </div>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full capitalize
                {{ $rec->status === 'present' ? 'bg-green-100 text-green-700' :
                   ($rec->status === 'absent' ? 'bg-red-100 text-red-600' :
                   ($rec->status === 'late' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700')) }}">
                {{ $rec->status }}
            </span>
        </div>
        @endforeach
    </div>
    @else
    <div class="px-6 py-12 text-center text-gray-400 text-sm">No attendance records for this month.</div>
    @endif
</div>
@endsection
