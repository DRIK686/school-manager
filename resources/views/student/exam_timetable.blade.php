@extends('layouts.student')
@section('title','Exam Timetable')
@section('page-title','Exam Timetable')
@section('content')

@if($examTypes->isEmpty())
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center text-gray-400 text-sm">
    No exam timetable has been published for {{ $student->schoolClass?->name }} yet.
</div>
@else
<div class="space-y-6">
    @foreach($examTypes as $entry)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">{{ $entry['exam']->name }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $student->schoolClass?->name }} — {{ $year?->name }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-2.5 font-semibold text-gray-600">Subject</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-gray-600">Date</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-gray-600">Time</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-gray-600">Room</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($entry['schedules'] as $schedule)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-gray-800">{{ $schedule->subject->name }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $schedule->exam_date ? $schedule->exam_date->format('d M Y') : '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-600">
                            @if($schedule->start_time)
                                {{ $schedule->start_time }}@if($schedule->end_time) – {{ $schedule->end_time }}@endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $schedule->room ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
