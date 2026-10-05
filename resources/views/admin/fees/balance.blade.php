@extends('layouts.admin')
@section('title', 'Balance Fees Report')
@section('content')

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex gap-3 items-end">
        <div class="w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">Filter by Class</label>
            <select name="class_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary px-4 py-2 text-sm">Filter</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800">Students with Outstanding Balances</h3>
        <span class="text-xs text-gray-500">{{ $students->count() }} student(s)</span>
    </div>
    @if($students->count())
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Student</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Class</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total Fee</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Paid</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Balance</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $s)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-6 py-3">
                    <p class="font-medium text-gray-800">{{ $s->full_name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $s->admission_no }}</p>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $s->schoolClass?->name }}</td>
                <td class="px-4 py-3 text-right text-gray-600">
                    {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($s->total_fee,2) }}
                </td>
                <td class="px-4 py-3 text-right text-green-600 font-medium">
                    {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($s->total_paid,2) }}
                </td>
                <td class="px-4 py-3 text-right text-red-600 font-bold">
                    {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($s->total_balance,2) }}
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('admin.fees.collect') }}?student_id={{ $s->id }}"
                        class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">Collect</a>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-gray-50 border-t border-gray-200">
                <td colspan="2" class="px-6 py-3 font-semibold text-gray-700">Total</td>
                <td class="px-4 py-3 text-right font-semibold">{{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($students->sum('total_fee'),2) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-green-600">{{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($students->sum('total_paid'),2) }}</td>
                <td class="px-4 py-3 text-right font-bold text-red-600">{{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($students->sum('total_balance'),2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="py-16 text-center text-gray-400 text-sm">No outstanding balances. 🎉</div>
    @endif
</div>
@endsection
