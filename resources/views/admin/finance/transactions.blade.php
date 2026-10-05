@extends('layouts.admin')
@section('title', $type === 'expense' ? 'Expenses' : 'Other Income')
@section('content')
@php $cur = \App\Models\SchoolSetting::current()->currency_symbol; $label = $type === 'expense' ? 'Expense' : 'Income'; @endphp
@include('admin.finance._nav')
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-3 text-sm">Record {{ strtolower($label) }}</h3>
    <form method="POST" action="{{ route('admin.finance.transactions.store') }}" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-end">@csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input name="txn_date" type="date" value="{{ old('txn_date', now()->toDateString()) }}" required class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <select name="finance_category_id" required class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"><option value="">Category</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
        <select name="money_account_id" required class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $type==='expense' ? 'Paid from' : 'Paid into' }}: {{ $a->name }}</option>@endforeach</select>
        <input name="amount" type="number" step="0.01" min="0.01" required placeholder="Amount" value="{{ old('amount') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="payee" placeholder="{{ $type==='expense' ? 'Paid to' : 'Received from' }}" value="{{ old('payee') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="reference_no" placeholder="Receipt / Ref no." value="{{ old('reference_no') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="description" placeholder="Description" value="{{ old('description') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input name="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf" class="text-xs">
        <button class="btn-primary px-4 py-2 text-sm">Save</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end mb-4">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <select name="finance_category_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"><option value="">All categories</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ request('finance_category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
        <select name="money_account_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"><option value="">All accounts</option>@foreach($accounts as $a)<option value="{{ $a->id }}" {{ request('money_account_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach</select>
        <button class="btn-primary px-4 py-2 text-sm">Filter</button>
        <span class="ml-auto text-sm">Total: <strong>{{ $cur }}{{ number_format($total, 2) }}</strong></span>
    </form>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2">Date</th><th>Category</th><th>Payee / Description</th><th>Ref</th><th>Account</th><th class="text-right">Amount</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $t)
            <tr class="border-t border-gray-100">
                <td class="py-2 {{ in_array($t->status,['void','rejected']) ? 'line-through opacity-50' : '' }}">{{ $t->txn_date->format('d M Y') }}</td>
                <td class="{{ in_array($t->status,['void','rejected']) ? 'line-through opacity-50' : '' }}">{{ $t->category->name ?? '' }}</td>
                <td class="{{ in_array($t->status,['void','rejected']) ? 'line-through opacity-50' : '' }}">{{ $t->payee }} {{ $t->description ? '— '.$t->description : '' }}
                    @if($t->attachment_path)<a href="{{ asset('storage/'.$t->attachment_path) }}" target="_blank" class="text-xs underline">[file]</a>@endif</td>
                <td>{{ $t->reference_no }}</td>
                <td>{{ $t->account->name ?? '' }}</td>
                <td class="text-right {{ in_array($t->status,['void','rejected']) ? 'line-through opacity-50' : '' }}">{{ $cur }}{{ number_format($t->amount, 2) }}</td>
                <td class="text-right whitespace-nowrap">
                @if($t->status==='active')
                    @if($t->type==='expense')<a href="{{ route('admin.finance.transactions.voucher', $t) }}" class="text-xs underline mr-2">Voucher</a>@endif
                    <details class="inline-block text-left"><summary class="cursor-pointer text-xs text-red-600">Void</summary>
<form method="POST" action="{{ route('admin.finance.transactions.void', $t) }}" class="flex gap-1 mt-1">@csrf<input name="void_reason" required placeholder="Reason" class="px-2 py-1 border border-gray-200 rounded text-xs"><button class="px-2 py-1 bg-red-600 text-white rounded text-xs">Confirm</button></form></details>
                @elseif($t->status==='pending')
                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">Pending approval</span>
                    @if(auth()->user()->hasAnyRole(['super_admin','admin']))
                        <form method="POST" action="{{ route('admin.finance.transactions.approve', $t) }}" class="inline">@csrf<button class="text-xs text-green-700 underline ml-2">Approve</button></form>
                        <details class="inline-block text-left ml-2"><summary class="cursor-pointer text-xs text-red-600">Reject</summary>
<form method="POST" action="{{ route('admin.finance.transactions.reject', $t) }}" class="flex gap-1 mt-1">@csrf<input name="void_reason" required placeholder="Reason" class="px-2 py-1 border border-gray-200 rounded text-xs"><button class="px-2 py-1 bg-red-600 text-white rounded text-xs">Confirm</button></form></details>
                    @endif
                @else
                    <span class="text-xs text-gray-400" title="{{ $t->void_reason }}">{{ $t->status }}</span>
                @endif
            </td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-4 text-gray-400">Nothing recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
