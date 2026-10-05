@extends('layouts.admin')
@section('title', 'Receipt — ' . $payment->receipt_no)
@section('content')

<div class="max-w-lg mx-auto">
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.fees.collect') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Fees
        </a>
        <a href="{{ route('admin.fees.receipt.pdf', $payment) }}"
            class="btn-primary px-5 py-2 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="receipt">
        {{-- Header --}}
        <div class="p-6 text-center border-b border-gray-100" style="background:var(--sidebar-bg)">
            @if($school->logo)
                <img src="{{ asset('storage/'.$school->logo) }}" class="h-12 mx-auto mb-2 object-contain">
            @endif
            <h2 class="text-white font-bold text-lg">{{ $school->school_name }}</h2>
            @if($school->address)<p class="text-white/70 text-xs">{{ $school->address }}</p>@endif
            @if($school->phone)<p class="text-white/70 text-xs">{{ $school->phone }}</p>@endif
            <div class="mt-3 inline-block bg-white/20 text-white text-xs font-bold px-4 py-1 rounded-full">
                OFFICIAL RECEIPT
            </div>
        </div>

        <div class="p-6">
            {{-- Receipt No & Date --}}
            <div class="flex justify-between items-start mb-6">
                <div>
                    <p class="text-xs text-gray-500">Receipt No</p>
                    <p class="text-lg font-bold font-mono" style="color:var(--sidebar-bg)">{{ $payment->receipt_no }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Date</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $payment->payment_date->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Student Info --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Student Details</p>
                <p class="text-sm font-bold text-gray-800">{{ $payment->student->full_name }}</p>
                <p class="text-xs text-gray-500">{{ $payment->student->admission_no }} • {{ $payment->student->schoolClass?->name }}</p>
                <p class="text-xs text-gray-500">Academic Year: {{ $payment->academicYear?->name }}</p>
            </div>

            {{-- Payment Details --}}
            <table class="w-full text-sm mb-6">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 text-xs text-gray-500 font-semibold">Description</th>
                        <th class="text-right py-2 text-xs text-gray-500 font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-3">{{ $payment->feeStructure->feeCategory->name }}</td>
                        <td class="py-3 text-right font-semibold">{{ $school->currency_symbol }}{{ number_format($payment->amount_paid,2) }}</td>
                    </tr>
                    @if($payment->fine_paid > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 text-red-500">Late Fine</td>
                        <td class="py-3 text-right text-red-500">{{ $school->currency_symbol }}{{ number_format($payment->fine_paid,2) }}</td>
                    </tr>
                    @endif
                    @if($payment->discount_amount > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 text-green-600">Discount</td>
                        <td class="py-3 text-right text-green-600">- {{ $school->currency_symbol }}{{ number_format($payment->discount_amount,2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td class="py-3 font-bold">Total Paid</td>
                        <td class="py-3 text-right font-bold text-lg" style="color:var(--sidebar-bg)">
                            {{ $school->currency_symbol }}{{ number_format($payment->amount_paid + $payment->fine_paid - $payment->discount_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- Payment Mode --}}
            <div class="flex justify-between text-sm bg-gray-50 rounded-lg p-3 mb-4">
                <span class="text-gray-500">Payment Mode</span>
                <span class="font-semibold capitalize">{{ str_replace('_',' ',$payment->payment_mode) }}</span>
            </div>
            @if($payment->reference_no)
            <div class="flex justify-between text-sm bg-gray-50 rounded-lg p-3 mb-4">
                <span class="text-gray-500">Reference</span>
                <span class="font-mono font-semibold">{{ $payment->reference_no }}</span>
            </div>
            @endif

            {{-- Footer --}}
            <div class="border-t border-gray-100 pt-4 mt-4 text-center">
                <p class="text-xs text-gray-400">Collected by: {{ $payment->collectedBy?->name }}</p>
                <p class="text-xs text-gray-400 mt-1">Thank you for your payment!</p>
            </div>
        </div>
    </div>
</div>
@endsection
