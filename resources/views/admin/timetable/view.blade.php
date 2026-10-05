<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Timetable — {{ $selectedClass?->name }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #333; padding: 20px; }
h1 { font-size: 15px; text-align: center; margin-bottom: 2px; }
.sub { text-align: center; color: #666; font-size: 11px; margin-bottom: 12px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: center; vertical-align: middle; }
th { background: #7f1d1d; color: white; font-size: 11px; }
.time-col { text-align: left; font-size: 10px; color: #555; min-width: 90px; }
.break-row td { background: #fef3c7; color: #92400e; font-weight: bold; }
.subject { font-weight: bold; font-size: 11px; }
.teacher { font-size: 9px; color: #666; margin-top: 2px; }
.filter-bar { margin-bottom: 12px; }
.filter-bar select { padding: 4px 8px; font-size: 12px; border: 1px solid #ccc; border-radius: 4px; }
.filter-bar button { padding: 4px 12px; font-size: 12px; background: #7f1d1d; color: white; border: none; border-radius: 4px; cursor: pointer; }
@media print {
    .filter-bar, .no-print { display: none; }
    body { padding: 10px; }
}
</style>
</head>
<body>

<div class="filter-bar no-print">
    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <select name="class_id" onchange="this.form.submit()">
            <option value="">Select class</option>
            @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @endforeach
        </select>
        @if($sections->count())
        <select name="section_id" onchange="this.form.submit()">
            <option value="">All Sections</option>
            @foreach($sections as $sec)
            <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
            @endforeach
        </select>
        @endif
        <button type="button" onclick="window.print()">🖨 Print</button>
        <a href="{{ route('admin.timetable.index', ['class_id' => $classId]) }}"
           style="font-size:12px;color:#7f1d1d;">← Back to Edit</a>
    </form>
</div>

<h1>{{ $school->school_name ?? config('app.name', 'SchoolManager') }} — Class Timetable</h1>
<p class="sub">
    {{ $selectedClass?->name }}
    @if($sectionId) — Section {{ $sections->firstWhere('id',$sectionId)?->name }} @endif
    | {{ $year?->name }}
</p>

@if(!$classId)
<p style="text-align:center;color:#999;margin-top:40px">Select a class to view timetable.</p>
@else
<table>
    <thead>
        <tr>
            <th class="time-col">Time</th>
            @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'] as $d => $dName)
            <th>{{ $dName }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($slots as $slot)
        <tr class="{{ $slot->is_break ? 'break-row' : '' }}">
            <td class="time-col" style="text-align:left">
                <strong>{{ $slot->label }}</strong><br>
                <span style="color:#999">
                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                    – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                </span>
            </td>
            @foreach([1,2,3,4,5] as $dayNo)
            @php $entry = $grid[$dayNo][$slot->id] ?? null; @endphp
            <td>
                @if($slot->is_break)
                    {{ $entry?->custom_label ?? $slot->label }}
                @elseif($entry)
                    <div class="subject">{{ $entry->subject?->name ?? '—' }}</div>
                    @if($entry->teacher)
                    <div class="teacher">{{ $entry->teacher->name }}</div>
                    @endif
                @else
                    <span style="color:#ccc">—</span>
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<p style="text-align:center;color:#999;font-size:10px;margin-top:16px">
    Generated on {{ now()->format('d M Y') }} — {{ $school->school_name ?? 'SchoolManager' }}
</p>
</body>
</html>
