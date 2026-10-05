@extends('layouts.admin')
@section('title', 'Finance Overview')
@section('content')
@php $cur = \App\Models\SchoolSetting::current()->currency_symbol; @endphp
@include('admin.finance._nav')
@if(($pending ?? 0) > 0 && auth()->user()->hasAnyRole(['super_admin','admin']))
<a href="{{ route('admin.finance.transactions','expense') }}" class="block mb-4 p-3 bg-yellow-50 text-yellow-800 rounded-lg text-sm">{{ $pending }} expense(s) waiting for your approval →</a>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @foreach($accounts as $a)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="text-xs text-gray-500">{{ $a->name }} <span class="text-gray-400">({{ \App\Models\MoneyAccount::TYPES[$a->type] ?? $a->type }})</span></div>
        <div class="text-2xl font-bold mt-1">{{ $cur }}{{ number_format($a->current_balance, 2) }}</div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="text-sm text-gray-500 mb-3">This month ({{ now()->format('F Y') }}) · Total money held: <strong>{{ $cur }}{{ number_format($totalBalance, 2) }}</strong></div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div><div class="text-gray-500">Fees collected</div><div class="text-lg font-semibold text-green-700">{{ $cur }}{{ number_format($feeIn, 2) }}</div></div>
        <div><div class="text-gray-500">Other income</div><div class="text-lg font-semibold text-green-700">{{ $cur }}{{ number_format($otherIn, 2) }}</div></div>
        <div><div class="text-gray-500">Expenses</div><div class="text-lg font-semibold text-red-700">{{ $cur }}{{ number_format($expense, 2) }}</div></div>
        <div><div class="text-gray-500">Surplus / (Deficit)</div><div class="text-lg font-semibold">{{ $cur }}{{ number_format($feeIn + $otherIn - $expense, 2) }}</div></div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-3 text-sm">Recent entries</h3>
    <table class="w-full text-sm"><tbody>
    @forelse($recent as $t)
        <tr class="border-t border-gray-100 {{ $t->status==='void' ? 'opacity-50 line-through' : '' }}">
            <td class="py-2">{{ $t->txn_date->format('d M Y') }}</td>
            <td>{{ ucfirst($t->type) }}</td>
            <td>{{ $t->category->name ?? '' }} {{ $t->payee ? '— '.$t->payee : '' }}</td>
            <td>{{ $t->account->name ?? '' }}</td>
            <td class="text-right">{{ $cur }}{{ number_format($t->amount, 2) }}</td>
        </tr>
    @empty
        <tr><td class="py-3 text-gray-400">No expenses or other income recorded yet.</td></tr>
    @endforelse
    </tbody></table>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-2 text-sm">Period lock</h3>
    @php $locked = \App\Http\Controllers\Admin\FinanceController::lockedUntil(); @endphp
    <p class="text-sm text-gray-600 mb-2">{{ $locked ? 'Books are locked up to '.$locked->format('d M Y').'. Nothing on or before that date can be added or voided.' : 'Books are open. Lock a closed month or term so figures already reported cannot change.' }}</p>
    @if(auth()->user()->hasAnyRole(['super_admin','admin']))
    <form method="POST" action="{{ route('admin.finance.lock') }}" class="flex flex-wrap gap-3 items-end">@csrf
        <input type="date" name="locked_until" value="{{ $locked?->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <button class="btn-primary px-4 py-2 text-sm">Save lock date</button>
        <span class="text-xs text-gray-500">Clear the date and save to unlock.</span>
    </form>
    @endif
</div>

@if(auth()->user()->hasAnyRole(['super_admin','admin']))
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-2 text-sm">Expense approval</h3>
    @php $th = \App\Models\SchoolSetting::current()->finance_approval_threshold ?? null; @endphp
    <p class="text-sm text-gray-600 mb-2">{{ $th !== null ? 'Expenses of '.$cur.number_format((float)$th,2).' and above entered by the accountant wait for admin approval.' : 'Approval is off. Set an amount to require admin approval for larger expenses entered by the accountant.' }}</p>
    <form method="POST" action="{{ route('admin.finance.approval') }}" class="flex flex-wrap gap-3 items-end">@csrf
        <input type="number" step="0.01" min="0" name="threshold" value="{{ $th }}" placeholder="Threshold amount" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <button class="btn-primary px-4 py-2 text-sm">Save</button>
        <span class="text-xs text-gray-500">Clear the box and save to turn approval off.</span>
    </form>
</div>
@endif
@endsection
