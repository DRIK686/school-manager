@extends('layouts.student')
@section('title','Attendance')
@section('page-title','My Attendance')
@section('content')
<div class="space-y-4">
    {{-- Month filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
                <select name="month" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected':'' }}>
                        {{ \Carbon\Carbon::createFromDate(2000,$m,1)->format('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                <select name="year" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    @foreach([date('Y')-1, date('Y')] as $y)
                    <option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-4 gap-3">
        @foreach(['present'=>['text-green-600','Present'],'absent'=>['text-red-500','Absent'],'late'=>['text-amber-500','Late'],'holiday'=>['text-blue-500','Holiday']] as $status=>[$color,$label])
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400">{{ $label }}</p>
            <p class="text-2xl font-bold {{ $color }} mt-1">{{ $summary[$status] ?? 0 }}</p>
        </div>
        @endforeach
    </div>

    {{-- Calendar grid --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">
            {{ \Carbon\Carbon::createFromDate($year,$month,1)->format('F Y') }}
        </h2>
        <div class="grid grid-cols-7 gap-1 text-center text-xs">
            @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
            <div class="font-semibold text-gray-400 py-1">{{ $d }}</div>
            @endforeach
            @php $firstDay = \Carbon\Carbon::createFromDate($year,$month,1)->dayOfWeek; @endphp
            @for($i=0;$i<$firstDay;$i++)<div></div>@endfor
            @for($day=1;$day<=$daysInMonth;$day++)
            @php $rec = $records->get($day); @endphp
            <div class="py-1.5 rounded-lg text-xs font-medium
                @if($rec)
                    @if($rec->status==='present') bg-green-100 text-green-700
                    @elseif($rec->status==='absent') bg-red-100 text-red-700
                    @elseif($rec->status==='late') bg-amber-100 text-amber-700
                    @else bg-blue-100 text-blue-700 @endif
                @else text-gray-400 @endif">
                {{ $day }}
            </div>
            @endfor
        </div>
    </div>
</div>
@endsection
