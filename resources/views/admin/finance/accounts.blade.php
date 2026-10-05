@extends('layouts.admin')
@section('title', 'Accounts & Transfers')
@section('content')
@php $cur = \App\Models\SchoolSetting::current()->currency_symbol; @endphp
@include('admin.finance._nav')
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-3 text-sm">Money accounts</h3>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2">Name</th><th>Type</th><th class="text-right">Opening</th><th class="text-right">Balance</th><th></th></tr></thead>
        <tbody>
        @foreach($accounts as $a)
            <tr class="border-t border-gray-100 {{ $a->is_active ? '' : 'opacity-50' }}">
                <td class="py-2">{{ $a->name }}</td>
                <td>{{ \App\Models\MoneyAccount::TYPES[$a->type] ?? $a->type }}</td>
                <td class="text-right">{{ $cur }}{{ number_format($a->opening_balance, 2) }}</td>
                <td class="text-right font-medium">{{ $cur }}{{ number_format($a->current_balance, 2) }}</td>
                <td class="text-right">
                    <details class="inline-block text-left align-top mr-3"><summary class="cursor-pointer text-xs text-gray-600 underline">Edit</summary>
                        <form method="POST" action="{{ route('admin.finance.accounts.update', $a) }}" class="mt-1 flex flex-col gap-1 w-56">@csrf
                            <input name="name" value="{{ $a->name }}" required class="px-2 py-1 border border-gray-200 rounded text-xs">
                            <label class="text-xs text-gray-500">Opening balance</label>
                            <input name="opening_balance" type="number" step="0.01" min="0" value="{{ $a->opening_balance }}" required class="px-2 py-1 border border-gray-200 rounded text-xs">
                            <label class="text-xs text-gray-500">As at</label>
                            <input name="opening_date" type="date" value="{{ optional($a->opening_date)->toDateString() }}" class="px-2 py-1 border border-gray-200 rounded text-xs">
                            <button class="px-2 py-1 bg-gray-800 text-white rounded text-xs">Save</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('admin.finance.accounts.toggle', $a) }}" class="inline">@csrf<button class="text-xs text-gray-600 underline">{{ $a->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-3 text-sm">Add account</h3>
    <form method="POST" action="{{ route('admin.finance.accounts.store') }}" class="flex flex-wrap gap-3 items-end">@csrf
        <input name="name" required placeholder="Name e.g. GCB Bank" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">@foreach(\App\Models\MoneyAccount::TYPES as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
        <input name="opening_balance" type="number" step="0.01" min="0" placeholder="Opening balance" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="opening_date" type="date" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <button class="btn-primary px-4 py-2 text-sm">Add</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-3 text-sm">Transfer between accounts (e.g. bank deposit, MoMo cash-out)</h3>
    <form method="POST" action="{{ route('admin.finance.transfers.store') }}" class="flex flex-wrap gap-3 items-end">@csrf
        <select name="from_account_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">@foreach($accounts->where('is_active',true) as $a)<option value="{{ $a->id }}">From: {{ $a->name }}</option>@endforeach</select>
        <select name="to_account_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">@foreach($accounts->where('is_active',true) as $a)<option value="{{ $a->id }}">To: {{ $a->name }}</option>@endforeach</select>
        <input name="amount" type="number" step="0.01" min="0.01" required placeholder="Amount" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="transfer_date" type="date" value="{{ now()->toDateString() }}" required class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="reference_no" placeholder="Reference" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="note" placeholder="Note" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <button class="btn-primary px-4 py-2 text-sm">Transfer</button>
    </form>
    <table class="w-full text-sm mt-4"><tbody>
    @foreach($transfers as $tr)
        <tr class="border-t border-gray-100 {{ $tr->status==='void' ? 'opacity-50 line-through' : '' }}">
            <td class="py-2">{{ $tr->transfer_date->format('d M Y') }}</td>
            <td>{{ $tr->from->name ?? '' }} → {{ $tr->to->name ?? '' }}</td>
            <td class="text-right">{{ $cur }}{{ number_format($tr->amount, 2) }}</td>
            <td class="text-right">@if($tr->status==='active')<details class="inline-block text-left"><summary class="cursor-pointer text-xs text-red-600">Void</summary>
<form method="POST" action="{{ route('admin.finance.transfers.void', $tr) }}" class="flex gap-1 mt-1">@csrf<input name="void_reason" required placeholder="Reason" class="px-2 py-1 border border-gray-200 rounded text-xs"><button class="px-2 py-1 bg-red-600 text-white rounded text-xs">Confirm</button></form></details>@endif</td>
        </tr>
    @endforeach
    </tbody></table>
</div>
@endsection
