@extends('layouts.admin')
@section('title', 'Budget')
@section('content')
@php $cur = \App\Models\SchoolSetting::current()->currency_symbol; $tb = 0; $ta = 0; @endphp
@include('admin.finance._nav')
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>@endif
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <select name="academic_year_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">@foreach($years as $y)<option value="{{ $y->id }}" {{ $year && $year->id==$y->id ? 'selected':'' }}>{{ $y->name }}</option>@endforeach</select>
        <button class="btn-primary px-4 py-2 text-sm">Show</button>
        <span class="text-xs text-gray-500">Actual spend counted from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}.</span>
    </form>
</div>
@if($year)
<form method="POST" action="{{ route('admin.finance.budget.save') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">@csrf
    <input type="hidden" name="academic_year_id" value="{{ $year->id }}">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2">Category</th><th class="w-40">Budget</th><th class="text-right">Actual</th><th class="text-right">Remaining</th><th class="w-40">Used</th></tr></thead>
        <tbody>
        @foreach($categories as $c)
            @php $b = (float) ($budgets[$c->id] ?? 0); $a = (float) ($actual[$c->id] ?? 0); $tb += $b; $ta += $a; $pct = $b > 0 ? round($a / $b * 100) : null; @endphp
            <tr class="border-t border-gray-100">
                <td class="py-2">{{ $c->name }}</td>
                <td><input type="number" step="0.01" min="0" name="amounts[{{ $c->id }}]" value="{{ isset($budgets[$c->id]) ? $budgets[$c->id] : '' }}" class="w-36 px-2 py-1 border border-gray-200 rounded text-sm"></td>
                <td class="text-right">{{ $cur }}{{ number_format($a, 2) }}</td>
                <td class="text-right pr-6 {{ $b > 0 && $a > $b ? 'text-red-700' : '' }}">{{ $b > 0 ? $cur . number_format($b - $a, 2) : '—' }}</td>
                <td>@if($pct !== null)<div class="h-2 bg-gray-100 rounded"><div class="h-2 rounded {{ $pct > 100 ? 'bg-red-500' : ($pct > 85 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min(100, $pct) }}%"></div></div><span class="text-xs text-gray-500">{{ $pct }}%</span>@endif</td>
            </tr>
        @endforeach
            <tr class="border-t-2 border-gray-300 font-semibold"><td class="py-2">Total</td><td>{{ $cur }}{{ number_format($tb, 2) }}</td><td class="text-right">{{ $cur }}{{ number_format($ta, 2) }}</td><td class="text-right">{{ $tb > 0 ? $cur . number_format($tb - $ta, 2) : '—' }}</td><td></td></tr>
        </tbody>
    </table>
    <button class="btn-primary px-4 py-2 text-sm mt-4">Save budget</button>
    <span class="text-xs text-gray-500 ml-2">Leave a box empty for no budget on that category.</span>
</form>
@endif
@endsection
