@extends('layouts.admin')
@section('title', 'Income & Expenditure')
@section('content')
@php $cur = \App\Models\SchoolSetting::current()->currency_symbol; @endphp
@include('admin.finance._nav')
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>@endif
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div><label class="block text-xs text-gray-600 mb-1">From</label><input type="date" name="date_from" value="{{ $from->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"></div>
        <div><label class="block text-xs text-gray-600 mb-1">To</label><input type="date" name="date_to" value="{{ $to->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"></div>
        <button class="btn-primary px-4 py-2 text-sm">Show</button>
        <a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="px-4 py-2 text-sm border border-gray-200 rounded-lg">PDF</a>
        <a href="{{ request()->fullUrlWithQuery(['export'=>'csv']) }}" class="px-4 py-2 text-sm border border-gray-200 rounded-lg">CSV</a>
    </form>
    <p class="text-xs text-gray-500 mt-2">Cash basis: money actually received and paid in the period.</p>
</div>

<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <h3 class="font-semibold mb-3 text-sm text-green-700">Income</h3>
        <table class="w-full text-sm"><tbody>
            @foreach($fees as $r)<tr class="border-t border-gray-100"><td class="py-1.5">Fees: {{ $r->name }}</td><td class="text-right">{{ $cur }}{{ number_format($r->amount, 2) }}</td></tr>@endforeach
            @if($fines > 0)<tr class="border-t border-gray-100"><td class="py-1.5">Fines & penalties</td><td class="text-right">{{ $cur }}{{ number_format($fines, 2) }}</td></tr>@endif
            @foreach($other as $r)<tr class="border-t border-gray-100"><td class="py-1.5">{{ $r->name }}</td><td class="text-right">{{ $cur }}{{ number_format($r->amount, 2) }}</td></tr>@endforeach
            <tr class="border-t-2 border-gray-300 font-semibold"><td class="py-2">Total income</td><td class="text-right">{{ $cur }}{{ number_format($incomeTotal, 2) }}</td></tr>
        </tbody></table>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <h3 class="font-semibold mb-3 text-sm text-red-700">Expenditure</h3>
        <table class="w-full text-sm"><tbody>
            @forelse($exp as $r)<tr class="border-t border-gray-100"><td class="py-1.5">{{ $r->name }}</td><td class="text-right">{{ $cur }}{{ number_format($r->amount, 2) }}</td></tr>
            @empty<tr><td class="py-1.5 text-gray-400">No expenses in this period.</td></tr>@endforelse
            <tr class="border-t-2 border-gray-300 font-semibold"><td class="py-2">Total expenditure</td><td class="text-right">{{ $cur }}{{ number_format($expenseTotal, 2) }}</td></tr>
        </tbody></table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 text-lg font-semibold flex justify-between">
    <span>Surplus / (Deficit)</span>
    <span class="{{ $surplus < 0 ? 'text-red-700' : 'text-green-700' }}">{{ $cur }}{{ number_format($surplus, 2) }}</span>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-1 text-sm">Fee position — {{ $year->name ?? 'current academic year' }}</h3>
    <p class="text-xs text-gray-500 mb-3">Fees billed to active students vs collected. Outstanding fees are not counted as income above.</p>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2">Fee</th><th class="text-right">Billed</th><th class="text-right">Collected</th><th class="text-right">Outstanding</th></tr></thead>
        <tbody>
        @php $b=0;$c=0;$o=0; @endphp
        @foreach($position as $name => $r)
            @php $b+=$r['billed'];$c+=$r['collected'];$o+=$r['outstanding']; @endphp
            <tr class="border-t border-gray-100"><td class="py-1.5">{{ $name }}</td><td class="text-right">{{ number_format($r['billed'],2) }}</td><td class="text-right">{{ number_format($r['collected'],2) }}</td><td class="text-right">{{ number_format($r['outstanding'],2) }}</td></tr>
        @endforeach
            <tr class="border-t-2 border-gray-300 font-semibold"><td class="py-2">Total</td><td class="text-right">{{ number_format($b,2) }}</td><td class="text-right">{{ number_format($c,2) }}</td><td class="text-right">{{ number_format($o,2) }}</td></tr>
        </tbody>
    </table>
    <a href="{{ route('admin.fees.balance') }}" class="text-xs underline mt-3 inline-block">See who owes (debtors) →</a>
</div>
@endsection
