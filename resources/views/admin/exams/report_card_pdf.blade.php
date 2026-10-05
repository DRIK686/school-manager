<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #000; padding: 15px 20px; }

.header { text-align:center; margin-bottom: 10px; }
.header img { height: 80px; margin-bottom: 4px; }
.header .school-name { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #7f1d1d; }
.header .report-title { font-size: 13px; font-weight: bold; margin-top: 2px; }

.info-wrap { display: table; width: 100%; }
.info-grid-cell { display: table-cell; vertical-align: top; width: 82%; }
.photo-cell { display: table-cell; vertical-align: top; width: 18%; text-align: right; }
.student-photo { width: 75px; height: 90px; object-fit: cover; border: 1px solid #000; }
.photo-placeholder { width: 75px; height: 90px; border: 1px solid #999; display: inline-block; text-align: center; font-size: 8px; color: #999; line-height: 90px; }
.info-grid { display: table; width: 100%; margin: 10px 0; border-collapse: collapse; }
.info-row { display: table-row; }
.info-cell { display: table-cell; padding: 3px 6px 3px 0; font-size: 11px; width: 50%; }
.info-label { font-weight: normal; }
.info-value { font-weight: bold; text-decoration: underline; display: inline-block; min-width: 120px; }

.divider { border-top: 2px solid #000; margin: 6px 0; }
.thin-divider { border-top: 1px solid #999; margin: 4px 0; }

table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10.5px; }
table th { border: 1px solid #000; padding: 5px 4px; text-align: center; font-weight: bold; background: #f0f0f0; }
table td { border: 1px solid #000; padding: 4px; }
table .subject-col { text-align: left; padding-left: 6px; }
table .num-col { text-align: center; }

.bottom-section { margin-top: 8px; }
.bottom-row { display: flex; gap: 16px; margin: 4px 0; font-size: 11px; }
.bottom-label { font-weight: bold; min-width: 110px; }
.bottom-value { border-bottom: 1px solid #000; flex: 1; min-height: 14px; }

.remarks-box { margin: 6px 0; }
.remarks-label { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
.remarks-content { border: 1px solid #ccc; padding: 6px; min-height: 50px; font-size: 11px; line-height: 1.5; }

.sig-section { margin-top: 16px; display: flex; gap: 30px; }
.sig-item { flex: 1; }
.sig-name { font-size: 11px; margin-bottom: 16px; }
.sig-line { border-top: 1px solid #000; padding-top: 3px; font-size: 10px; text-align: center; }

.grade-legend { margin-top: 10px; border-top: 1px solid #999; padding-top: 6px; font-size: 9.5px; color: #333; }
.grade-legend strong { font-size: 10px; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    @if($school->logo && file_exists(public_path('storage/'.$school->logo)))
        <img src="{{ public_path('storage/'.$school->logo) }}"><br>
    @endif
    <div class="school-name">{{ $school->school_name }}</div>
    <div class="report-title">{{ strtoupper($exam->name) }} REPORT</div>
</div>

<div class="divider"></div>

{{-- STUDENT INFO GRID (with photo) --}}
<div class="info-wrap">
<div class="info-grid-cell">
<div class="info-grid">
    <div class="info-row">
        <div class="info-cell">
            NAME: <span class="info-value">{{ strtoupper($student->full_name) }}</span>
        </div>
        <div class="info-cell">
            ID NUMBER: <span class="info-value">{{ $student->admission_no }}</span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            TERM / YEAR: <span class="info-value">{{ strtoupper($exam->name) }}, {{ $year?->name }}</span>
        </div>
        <div class="info-cell">
            ATTENDANCE: <span class="info-value">
                @if($termReport?->attendance_present)
                    {{ $termReport->attendance_present }} out of {{ $termReport->attendance_total ?? '—' }}
                @else
                    — out of —
                @endif
            </span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            CLASS: <span class="info-value">{{ strtoupper(($historicalClass ?? $student->schoolClass)?->name ?? '—') }}</span>
        </div>
        <div class="info-cell">
            NUMBER ON ROLL: <span class="info-value">{{ $total_students }}</span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            VACATION: <span class="info-value">
                {{ $termReport?->vacation_date ? strtoupper($termReport->vacation_date->format('jS F, Y')) : '—' }}
            </span>
        </div>
        <div class="info-cell">
            RE-OPENING: <span class="info-value">
                {{ $termReport?->reopening_date ? strtoupper($termReport->reopening_date->format('jS F, Y')) : '—' }}
            </span>
        </div>
    </div>
</div>
</div>
<div class="photo-cell">
    @if($student->profile_photo && file_exists(public_path('storage/'.$student->profile_photo)))
        <img src="{{ public_path('storage/'.$student->profile_photo) }}" class="student-photo">
    @else
        <div class="photo-placeholder">PHOTO</div>
    @endif
</div>
</div>

<div class="divider"></div>

{{-- MARKS TABLE --}}
<table>
    <thead>
        <tr>
            <th class="subject-col" style="width:28%">SUBJECT</th>
            <th style="width:10%">SBA Score<br>50%</th>
            <th style="width:10%">Exam<br>Score 50%</th>
            <th style="width:9%">Total<br>Score</th>
            <th style="width:7%">Grade</th>
            <th style="width:10%">Class<br>Average</th>
            <th style="width:26%">Remarks on areas of<br>Strengths and Weaknesses</th>
        </tr>
    </thead>
    <tbody>
        @foreach($schedules as $schedule)
        @php $mark = $marks->get($schedule->id); @endphp
        <tr>
            <td class="subject-col">{{ $schedule->subject->name }}</td>
            <td class="num-col">
                @if($mark?->is_absent) ABS
                @elseif($mark?->sba_score !== null) {{ number_format($mark->sba_score, 1) }}
                @else —
                @endif
            </td>
            <td class="num-col">
                @if($mark?->is_absent) ABS
                @elseif($mark?->exam_score !== null) {{ number_format($mark->exam_score, 1) }}
                @else —
                @endif
            </td>
            <td class="num-col">
                @if($mark?->is_absent) ABS
                @elseif($mark?->marks_obtained !== null) {{ number_format($mark->marks_obtained, 1) }}
                @else —
                @endif
            </td>
            <td class="num-col" style="font-weight:bold">{{ $mark?->is_absent ? 'ABS' : ($mark?->grade ?? '—') }}</td>
            <td class="num-col">{{ $mark?->class_average ? number_format($mark->class_average, 1) : '—' }}</td>
            <td>{{ $mark?->is_absent ? 'Absent' : ($mark?->remarks ?? '—') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="subject-col" style="font-weight:bold">TOTAL</td>
            <td class="num-col" style="font-weight:bold">{{ number_format($marks->where('is_absent',false)->sum('sba_score'), 1) }}</td>
            <td class="num-col" style="font-weight:bold">{{ number_format($marks->where('is_absent',false)->sum('exam_score'), 1) }}</td>
            <td class="num-col" style="font-weight:bold">{{ number_format($total, 1) }}</td>
            <td class="num-col" style="font-weight:bold;color:#7f1d1d">{{ $grade?->grade ?? '—' }}</td>
            <td class="num-col"></td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="divider"></div>

{{-- PERFORMANCE SUMMARY --}}
<div style="margin: 8px 0;">
    <div style="font-weight:bold; font-size:11px; margin-bottom:4px;">Performance Summary</div>
    <table>
        <thead>
            <tr>
                <th>Expected Total</th>
                <th>Total Scored</th>
                <th>Highest Mark</th>
                <th>Lowest Mark</th>
                <th>Average Score</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="num-col">{{ number_format($maxTotal, 1) }}</td>
                <td class="num-col">{{ number_format($total, 1) }}</td>
                <td class="num-col">{{ $highestMark !== null ? number_format($highestMark, 1) : '—' }}</td>
                <td class="num-col">{{ $lowestMark !== null ? number_format($lowestMark, 1) : '—' }}</td>
                <td class="num-col">{{ number_format($avg, 1) }}</td>
                <td class="num-col" style="font-weight:bold; color:#7f1d1d">{{ $grade?->grade ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</div>

@if($termComparison->count() > 1)
<div class="thin-divider"></div>

{{-- TERM COMPARISON CHART --}}
<div style="margin: 8px 0;">
    <div style="font-weight:bold; font-size:11px; margin-bottom:6px;">Term-by-Term Progress ({{ $year?->name }})</div>
    <table style="border:none; margin:0;">
        <tr>
            @foreach($termComparison as $t)
            <td style="border:none; text-align:center; vertical-align:bottom; height:110px; width:{{ number_format(100 / $termComparison->count(), 2) }}%;">
                <div style="background:{{ $t['is_current'] ? '#7f1d1d' : '#c9a4a4' }}; height:{{ max(2, round($t['avg'])) }}px; margin:0 auto; width:36px;"></div>
            </td>
            @endforeach
        </tr>
        <tr>
            @foreach($termComparison as $t)
            <td style="border:none; text-align:center; font-size:9px; font-weight:bold; padding-top:2px;">{{ $t['avg'] }}%</td>
            @endforeach
        </tr>
        <tr>
            @foreach($termComparison as $t)
            <td style="border:none; text-align:center; font-size:8.5px; color:#555;">{{ $t['name'] }}{{ $t['is_current'] ? ' (Current)' : '' }}</td>
            @endforeach
        </tr>
    </table>
</div>
@endif

<div class="divider"></div>

{{-- BOTTOM SECTION --}}
<div class="bottom-section">
    <div class="bottom-row">
        <span class="bottom-label">Promoted to:</span>
        <span class="bottom-value">{{ $termReport?->promoted_to ?? '' }}</span>
    </div>

    <div class="bottom-row" style="margin-top:6px">
        <span class="bottom-label">Talent / Interest:</span>
        <span class="bottom-value">{{ $termReport?->talent_interest ?? '' }}</span>
    </div>

    <div class="bottom-row" style="margin-top:6px">
        <span class="bottom-label">Conduct:</span>
        <span class="bottom-value">{{ $termReport?->conduct ?? '' }}</span>
    </div>
</div>

<div class="remarks-box" style="margin-top:10px">
    <div class="remarks-label">Class Teacher's General Remarks</div>
    <div class="remarks-content">{{ $termReport?->teacher_remarks ?? '' }}</div>
</div>

{{-- SIGNATURES --}}
<div class="sig-section">
    <div class="sig-item">
        <div class="sig-name">
            <strong>Teacher:</strong> {{ $termReport?->class_teacher ?? $school->school_name }}
        </div>
        <div class="sig-line">Signature: ................................</div>
    </div>
    <div class="sig-item">
        <div class="sig-name">
            <strong>Principal:</strong> {{ $termReport?->principal ?? '' }}
        </div>
        <div class="sig-line">Signature: ................................</div>
    </div>
</div>

{{-- GRADE LEGEND --}}
<div class="grade-legend">
    <strong>Explanatory notes / Grades:</strong>
    @php $grades = \App\Models\GradeScale::where('academic_year_id', $year?->id)->orderByDesc('min_mark')->get(); @endphp
    @foreach($grades as $g)
        {{ $g->grade }}({{ $g->min_mark }}-{{ $g->max_mark }}) {{ $g->remark }}@if(!$loop->last) &nbsp;&nbsp; @endif
    @endforeach
</div>

</body>
</html>
