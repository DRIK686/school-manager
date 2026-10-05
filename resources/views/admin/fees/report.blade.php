@extends('layouts.admin')
@section('title', 'Fee Collection Report')
@section('content')

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Academic Year</label>
            <select name="academic_year_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}" {{ (isset($year) && $year->id == $ay->id) ? 'selected' : '' }}>
                    {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From Date</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To Date</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Payment Mode</label>
            <select name="payment_mode" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">All Modes</option>
                <option value="cash" {{ request('payment_mode')=='cash'?'selected':'' }}>Cash</option>
                <option value="mobile_money" {{ request('payment_mode')=='mobile_money'?'selected':'' }}>Mobile Money</option>
                <option value="bank" {{ request('payment_mode')=='bank'?'selected':'' }}>Bank</option>
                <option value="cheque" {{ request('payment_mode')=='cheque'?'selected':'' }}>Cheque</option>
            </select>
        </div>
        <button type="submit" class="btn-primary px-4 py-2 text-sm">Filter</button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase font-semibold">Total Collected</p>
        <p class="text-2xl font-bold mt-1" style="color:var(--sidebar-bg)">
            {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($totalCollected,2) }}
        </p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase font-semibold">Transactions</p>
        <p class="text-2xl font-bold mt-1 text-gray-800">{{ $payments->total() }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase font-semibold">Academic Year</p>
        <p class="text-2xl font-bold mt-1 text-gray-800">{{ $year?->name }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @if($payments->count())
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Receipt</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Student</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Fee Type</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Mode</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $p->receipt_no }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $p->student->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $p->student->schoolClass?->name }}</p>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $p->feeStructure->feeCategory->name }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 capitalize">
                        {{ str_replace('_',' ',$p->payment_mode) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $p->payment_date->format('d M Y') }}</td>
                <td class="px-4 py-3 text-right font-semibold" style="color:var(--sidebar-bg)">
                    {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($p->amount_paid,2) }}
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('admin.fees.receipt', $p) }}" class="text-xs text-gray-500 hover:text-gray-700">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $payments->links() }}</div>
    @else
    <div class="py-16 text-center text-gray-400 text-sm">No payments found.</div>
    @endif
</div>
@endsection
