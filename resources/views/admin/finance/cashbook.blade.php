@extends('layouts.admin')
@section('title', 'Cash Book')
@section('content')
@php $cur = \App\Models\SchoolSetting::current()->currency_symbol; @endphp
@include('admin.finance._nav')
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>@endif
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <select name="money_account_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">@foreach($accounts as $a)<option value="{{ $a->id }}" {{ $account && $account->id==$a->id ? 'selected':'' }}>{{ $a->name }}</option>@endforeach</select>
        <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <button class="btn-primary px-4 py-2 text-sm">Show</button>
    </form>
</div>
@if($account)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2">Date</th><th>Type</th><th>Ref</th><th>Description</th><th class="text-right">In</th><th class="text-right">Out</th><th class="text-right">Balance</th><th></th></tr></thead>
        <tbody>
            <tr class="border-t border-gray-100 bg-gray-50"><td colspan="6" class="py-2 font-medium">Opening balance</td><td class="text-right font-medium">{{ $cur }}{{ number_format($opening, 2) }}</td><td></td></tr>
            @foreach($rows as $r)
            <tr class="border-t border-gray-100">
                <td class="py-2">{{ $r['date']->format('d M Y') }}</td>
                <td>{{ $r['kind'] }}</td>
                <td>{{ $r['ref'] }}</td>
                <td>{{ $r['desc'] }}</td>
                <td class="text-right text-green-700">{{ $r['in'] ? number_format($r['in'], 2) : '' }}</td>
                <td class="text-right text-red-700">{{ $r['out'] ? number_format($r['out'], 2) : '' }}</td>
                <td class="text-right">{{ number_format($r['balance'], 2) }}</td>
                <td class="text-right"><details class="inline-block text-left"><summary class="cursor-pointer text-xs text-red-600">Void</summary>
<form method="POST" action="{{ $r['void_url'] }}" class="flex gap-1 mt-1">@csrf<input name="void_reason" required placeholder="Reason" class="px-2 py-1 border border-gray-200 rounded text-xs"><button class="px-2 py-1 bg-red-600 text-white rounded text-xs">Confirm</button></form></details></td>
            </tr>
            @endforeach
            <tr class="border-t-2 border-gray-300 font-semibold">
                <td colspan="4" class="py-2">Totals / Closing balance</td>
                <td class="text-right">{{ number_format($totalIn, 2) }}</td>
                <td class="text-right">{{ number_format($totalOut, 2) }}</td>
                <td class="text-right">{{ $cur }}{{ number_format($closing, 2) }}</td><td></td>
            </tr>
        </tbody>
    </table>
</div>
@endif
@endsection
