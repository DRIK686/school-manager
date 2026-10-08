@extends(auth()->user()->hasRole('teacher') ? 'layouts.teacher' : 'layouts.admin')
@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')
@section('content')

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="w-44">
            <label class="block text-xs font-medium text-gray-600 mb-1">Class <span class="text-red-500">*</span></label>
            <select name="class_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">Select class...</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
            <select name="month" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-24">
            <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
            <select name="year" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                @foreach([date('Y')-1, date('Y'), date('Y')+1] as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary px-5 py-2 text-sm">Generate Report</button>
    </form>
</div>

@if($data->count())
<style>
    @media print {
        body * { visibility: hidden; }
        #printable-report, #printable-report * { visibility: visible; }
        #printable-report { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>
<div id="printable-report" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">
                Attendance Report — {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F') }} {{ $year }}
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ count($daysInMonth) }} school days</p>
        </div>
        <button onclick="window.print()" class="no-print px-4 py-2 text-sm text-white rounded-lg flex items-center gap-2" style="background:var(--sidebar-bg)">
            🖨️ Print
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 sticky left-0 bg-gray-50 min-w-48">Student</th>
                    @foreach($daysInMonth as $day)
                    <th class="px-1 py-3 font-semibold text-gray-500 text-center min-w-8">
                        {{ \Carbon\Carbon::parse($day)->format('d') }}
                        <br><span class="text-gray-400 font-normal">{{ \Carbon\Carbon::parse($day)->format('D')[0] }}</span>
                    </th>
                    @endforeach
                    <th class="px-3 py-3 font-semibold text-gray-600 text-center">P</th>
                    <th class="px-3 py-3 font-semibold text-gray-600 text-center">A</th>
                    <th class="px-3 py-3 font-semibold text-gray-600 text-center">L</th>
                    <th class="px-3 py-3 font-semibold text-gray-600 text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-4 py-2 sticky left-0 bg-white">
                        <p class="font-medium text-gray-800">{{ $row['student']->full_name }}</p>
                        <p class="text-gray-400">{{ $row['student']->admission_no }}</p>
                    </td>
                    @foreach($daysInMonth as $day)
                    @php $rec = $row['records']->get($day); @endphp
                    <td class="px-1 py-2 text-center">
                        @if($rec)
                            <span class="inline-flex w-6 h-6 rounded-full items-center justify-center text-white text-xs font-bold cursor-default
                                {{ $rec->status === 'present' ? 'bg-green-500' :
                                   ($rec->status === 'absent' ? 'bg-red-500' :
                                   ($rec->status === 'late' ? 'bg-yellow-400' : 'bg-blue-400')) }}"
                                title="{{ ucfirst($rec->status) }}{{ $rec->taken_at ? ' — marked at ' . $rec->taken_at->format('g:i A') : '' }}">
                                {{ strtoupper(substr($rec->status,0,1)) }}
                            </span>
                        @else
                            <span class="text-gray-200">—</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-3 py-2 text-center font-semibold text-green-600">{{ $row['present'] }}</td>
                    <td class="px-3 py-2 text-center font-semibold text-red-500">{{ $row['absent'] }}</td>
                    <td class="px-3 py-2 text-center font-semibold text-yellow-500">{{ $row['late'] }}</td>
                    <td class="px-3 py-2 text-center">
                        <span class="font-bold {{ $row['pct'] >= 75 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $row['pct'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif($classId)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-400 text-sm">
    No attendance records for this class in {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F') }} {{ $year }}.
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center text-gray-400">
    <p class="text-sm">Select a class and month to generate the report.</p>
</div>
@endif
@endsection
