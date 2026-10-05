@extends('layouts.student')
@section('title','Timetable')
@section('page-title','My Timetable')
@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">{{ $student->schoolClass?->name }} — Weekly Timetable</h2>
    </div>
    <table class="w-full text-xs border-collapse min-w-[600px]">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-3 py-2 text-left border border-gray-100 font-semibold text-gray-600 w-24">Time</th>
                @foreach($days as $dayName)
                <th class="px-3 py-2 text-center border border-gray-100 font-semibold text-gray-600">{{ $dayName }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($slots as $slot)
            <tr class="{{ $slot->is_break ? 'bg-amber-50' : '' }}">
                <td class="px-3 py-2 border border-gray-100">
                    <div class="font-semibold {{ $slot->is_break ? 'text-amber-700' : 'text-gray-700' }}">{{ $slot->label }}</div>
                    <div class="text-gray-400">
                        {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                    </div>
                </td>
                @foreach(array_keys($days) as $dayNo)
                @php $entry = $grid[$dayNo][$slot->id] ?? null; @endphp
                <td class="px-3 py-2 border border-gray-100 text-center">
                    @if($slot->is_break)
                        <span class="text-amber-700">{{ $entry?->custom_label ?? $slot->label }}</span>
                    @elseif($entry)
                        <div class="font-semibold text-gray-800">{{ $entry->subject?->name }}</div>
                        @if($entry->teacher)<div class="text-gray-400 text-xs mt-0.5">{{ $entry->teacher->name }}</div>@endif
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
