@extends('layouts.student')
@section('title','My Fees')
@section('page-title','Fee Payments')
@section('content')
<div class="space-y-5">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold">Total Fees</p>
            <p class="text-2xl font-bold mt-1 text-gray-800">GH₵{{ number_format($totalFees, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $year?->name }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold">Total Paid</p>
            <p class="text-2xl font-bold mt-1 text-green-600">GH₵{{ number_format($totalPaid, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $structures->where('status','paid')->count() }} fee(s) cleared</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold">Outstanding</p>
            <p class="text-2xl font-bold mt-1 {{ $totalBalance > 0 ? 'text-red-500' : 'text-green-600' }}">
                GH₵{{ number_format($totalBalance, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $structures->where('status','unpaid')->count() + $structures->where('status','partial')->count() }} fee(s) pending</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold">Progress</p>
            @php $pct = $totalFees > 0 ? round($totalPaid / $totalFees * 100) : 0; @endphp
            <p class="text-2xl font-bold mt-1" style="color:var(--sidebar-bg)">{{ $pct }}%</p>
            <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-yellow-400' }}"
                     style="width:{{ $pct }}%"></div>
            </div>
        </div>
    </div>

    {{-- Fee Breakdown --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Fee Breakdown — {{ $year?->name }}</h2>
        </div>
        @if($structures->isEmpty())
        <div class="px-5 py-12 text-center text-gray-400 text-sm">No fee structures found for your class.</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($structures as $s)
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-medium text-gray-800">{{ $s->feeCategory?->name ?? 'Fee' }}</p>
                            @if($s->status === 'paid')
                                <span class="text-xs px-2 py-0.5 bg-green-50 text-green-700 rounded-full font-medium">✓ Paid</span>
                            @elseif($s->status === 'partial')
                                <span class="text-xs px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full font-medium">Partial</span>
                            @else
                                <span class="text-xs px-2 py-0.5 bg-red-50 text-red-600 rounded-full font-medium">Unpaid</span>
                            @endif
                        </div>
                        @if($s->due_date)
                        <p class="text-xs text-gray-400 mt-0.5">
                            Due: {{ \Carbon\Carbon::parse($s->due_date)->format('d M Y') }}
                            @if(\Carbon\Carbon::parse($s->due_date)->isPast() && $s->status !== 'paid')
                                <span class="text-red-500 ml-1">· Overdue</span>
                            @endif
                        </p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm text-gray-500">Total: <span class="font-medium text-gray-800">GH₵{{ number_format($s->amount, 2) }}</span></p>
                        <p class="text-sm text-green-600">Paid: <span class="font-medium">GH₵{{ number_format($s->paid_amount, 2) }}</span></p>
                        @if($s->balance > 0)
                        <p class="text-sm text-red-500">Balance: <span class="font-medium">GH₵{{ number_format($s->balance, 2) }}</span></p>
                        @endif
                    </div>
                </div>

                {{-- Progress bar per fee --}}
                @php $feePct = $s->amount > 0 ? min(100, round($s->paid_amount / $s->amount * 100)) : 0; @endphp
                <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all
                        {{ $feePct >= 100 ? 'bg-green-500' : ($feePct > 0 ? 'bg-amber-400' : 'bg-red-300') }}"
                         style="width:{{ $feePct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Total row --}}
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl flex items-center justify-between">
            <p class="font-semibold text-gray-700">Total</p>
            <div class="flex gap-6 text-sm">
                <span class="text-gray-500">Billed: <strong class="text-gray-800">GH₵{{ number_format($totalFees,2) }}</strong></span>
                <span class="text-green-600">Paid: <strong>GH₵{{ number_format($totalPaid,2) }}</strong></span>
                <span class="{{ $totalBalance > 0 ? 'text-red-500' : 'text-green-600' }}">
                    Balance: <strong>GH₵{{ number_format($totalBalance,2) }}</strong>
                </span>
            </div>
        </div>
        @endif
    </div>

    {{-- Payment History --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Payment History</h2>
        </div>
        @if($payments->isEmpty())
        <div class="px-5 py-10 text-center text-gray-400 text-sm">No payments recorded yet.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Fee Type</th>
                        <th class="px-5 py-3 text-left">Receipt No</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($payments as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500">
                            {{ \Carbon\Carbon::parse($p->paid_at ?? $p->created_at)->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3 text-gray-800">
                            {{ $p->feeStructure?->feeCategory?->name ?? 'Fee Payment' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                                {{ $p->receipt_no }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-green-600">
                            GH₵{{ number_format($p->amount_paid, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-5 py-3 font-semibold text-gray-700">Total Paid</td>
                        <td class="px-5 py-3 text-right font-bold text-green-600">
                            GH₵{{ number_format($payments->sum('amount_paid'), 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
