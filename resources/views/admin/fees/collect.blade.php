@extends('layouts.admin')
@section('title', 'Collect Fees')
@section('content')

{{-- Student Search --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-800 mb-4">Find Student</h3>
    <form method="GET" class="flex gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">Select Student</label>
            <select name="student_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">Search and select student...</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->first_name }} {{ $s->last_name }} ({{ $s->admission_no }})
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary px-6 py-2">Load Fees</button>
    </form>
</div>

@if($student)
{{-- Student Info Banner --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg font-bold flex-shrink-0"
            style="background:var(--sidebar-bg)">
            {{ strtoupper(substr($student->first_name,0,1)) }}
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $student->full_name }}</p>
            <p class="text-sm text-gray-500">
                <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $student->admission_no }}</span>
                &nbsp;•&nbsp; {{ $student->schoolClass?->name }}
                @if($student->section) / Section {{ $student->section->name }} @endif
            </p>
        </div>
        <div class="ml-auto text-right">
            <p class="text-xs text-gray-500">Total Balance</p>
            <p class="text-xl font-bold text-red-600">
                {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($structures->sum('balance'),2) }}
            </p>
        </div>
    </div>
</div>

{{-- Fee Items --}}
@if($structures->count())
<div class="space-y-4">
    @foreach($structures as $fee)
    <div class="bg-white rounded-xl shadow-sm border {{ $fee->is_paid ? 'border-green-100' : 'border-gray-100' }} overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 {{ $fee->is_paid ? 'bg-green-50' : '' }}">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-gray-800">{{ $fee->feeCategory->name }}</p>
                    @if($fee->is_paid)
                        <span class="px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 rounded-full">PAID</span>
                    @elseif($fee->paid_amount > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">PARTIAL</span>
                    @else
                        <span class="px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-600 rounded-full">UNPAID</span>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                    <span>Total: <strong>{{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($fee->amount,2) }}</strong></span>
                    @if($fee->discount_applied > 0)<span>Discount: <strong class="text-blue-600">-{{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($fee->discount_applied,2) }}</strong></span>@endif
                    <span>Paid: <strong class="text-green-600">{{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($fee->paid_amount,2) }}</strong></span>
                    <span>Balance: <strong class="text-red-600">{{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($fee->balance,2) }}</strong></span>
                    @if($fee->fine_amount > 0)
                        <span class="text-red-500">Fine: {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($fee->fine_amount,2) }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if(!$fee->is_paid)
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100" x-data="{ open: false }">
            <button @click="open=!open" type="button"
                class="text-sm font-medium flex items-center gap-2" style="color:var(--sidebar-bg)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Record Payment
            </button>
            <div x-show="open" x-cloak class="mt-4">
                <form method="POST" action="{{ route('admin.fees.collect.process') }}" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="fee_structure_id" value="{{ $fee->id }}">
                    <input type="hidden" name="fine_paid" value="{{ $fee->fine_amount }}">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Amount Paying <span class="text-red-500">*</span></label>
                        <input type="number" name="amount_paid" value="{{ $fee->balance }}" min="0.01" step="0.01" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Payment Mode <span class="text-red-500">*</span></label>
                        <select name="payment_mode" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Reference No</label>
                        <input type="text" name="reference_no" placeholder="Transaction ID..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Remarks</label>
                        <input type="text" name="remarks"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn-primary w-full py-2">Record & Print Receipt</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-400">
    <p class="text-sm">No fee structures set up for the current academic year.</p>
    <a href="{{ route('admin.fees.structures') }}" class="mt-3 inline-block btn-primary px-5 py-2 text-sm">Set Up Fee Structures</a>
</div>
@endif

@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center text-gray-400">
    <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <p class="text-sm">Select a student above to view and collect their fees.</p>
</div>
@endif
@endsection
