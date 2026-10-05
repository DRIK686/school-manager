<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:'DejaVu Sans',sans-serif;font-size:11px;color:#111}
h1{font-size:16px;margin:0}h2{font-size:12px;margin:14px 0 4px;border-bottom:1px solid #999;padding-bottom:2px}
table{width:100%;border-collapse:collapse}td,th{padding:3px 4px}.r{text-align:right}.t td{border-top:1px solid #333;font-weight:bold}
</style></head><body>
@php $cur = $school->currency_symbol; @endphp
<h1>{{ $school->school_name }}</h1>
<div>Income & Expenditure Statement (cash basis)<br>{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</div>
<h2>Income</h2>
<table>
@foreach($fees as $r)<tr><td>Fees: {{ $r->name }}</td><td class="r">{{ $cur }}{{ number_format($r->amount,2) }}</td></tr>@endforeach
@if($fines>0)<tr><td>Fines & penalties</td><td class="r">{{ $cur }}{{ number_format($fines,2) }}</td></tr>@endif
@foreach($other as $r)<tr><td>{{ $r->name }}</td><td class="r">{{ $cur }}{{ number_format($r->amount,2) }}</td></tr>@endforeach
<tr class="t"><td>Total income</td><td class="r">{{ $cur }}{{ number_format($incomeTotal,2) }}</td></tr>
</table>
<h2>Expenditure</h2>
<table>
@foreach($exp as $r)<tr><td>{{ $r->name }}</td><td class="r">{{ $cur }}{{ number_format($r->amount,2) }}</td></tr>@endforeach
<tr class="t"><td>Total expenditure</td><td class="r">{{ $cur }}{{ number_format($expenseTotal,2) }}</td></tr>
</table>
<h2>Surplus / (Deficit): {{ $cur }}{{ number_format($surplus,2) }}</h2>
<h2>Fee position — {{ $year->name ?? '' }}</h2>
<table><tr><th align="left">Fee</th><th class="r">Billed</th><th class="r">Collected</th><th class="r">Outstanding</th></tr>
@foreach($position as $name=>$r)<tr><td>{{ $name }}</td><td class="r">{{ number_format($r['billed'],2) }}</td><td class="r">{{ number_format($r['collected'],2) }}</td><td class="r">{{ number_format($r['outstanding'],2) }}</td></tr>@endforeach
</table>
<p style="margin-top:20px;font-size:9px;color:#666">Generated {{ now()->format('d M Y H:i') }}</p>
</body></html>
