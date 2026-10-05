@extends('layouts.admin')
@section('title', 'Fee Discounts')
@section('content')
@php $cur = \App\Models\SchoolSetting::current()->currency_symbol; @endphp
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-1 text-sm">Discount types</h3>
    <p class="text-xs text-gray-500 mb-3">A percentage applies to every fee item. A fixed amount is deducted from the student's fees until used up.</p>
    <form method="POST" action="{{ route('admin.fees.discounts.store') }}" class="flex flex-wrap gap-3 items-end mb-4">@csrf
        <input name="name" required placeholder="e.g. Staff child, Scholarship" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <select name="discount_type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"><option value="percent">Percent (%)</option><option value="fixed">Fixed amount</option></select>
        <input name="value" type="number" step="0.01" min="0.01" required placeholder="Value" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <button class="btn-primary px-4 py-2 text-sm">Add type</button>
    </form>
    <table class="w-full text-sm"><tbody>
    @forelse($discounts as $d)
        <tr class="border-t border-gray-100">
            <td class="py-2">{{ $d->name }}</td>
            <td>{{ $d->discount_type === 'percent' ? rtrim(rtrim(number_format($d->value, 2), '0'), '.') . '%' : $cur . number_format($d->value, 2) }}</td>
            <td class="text-xs text-gray-400">{{ $counts[$d->id] ?? 0 }} student(s)</td>
            <td class="text-right"><form method="POST" action="{{ route('admin.fees.discounts.destroy', $d->id) }}">@csrf @method('DELETE')<button class="text-xs text-red-600 underline">Delete</button></form></td>
        </tr>
    @empty
        <tr><td class="py-3 text-gray-400">No discount types yet.</td></tr>
    @endforelse
    </tbody></table>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <h3 class="font-semibold mb-3 text-sm">Assign to a student — {{ $year->name ?? 'no current year' }}</h3>
    <form method="POST" action="{{ route('admin.fees.discounts.assign') }}" class="flex flex-wrap gap-3 items-end mb-4">@csrf
        <select name="student_id" required class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 min-w-64"><option value="">Select student</option>
            @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }} ({{ $s->admission_no }})</option>@endforeach
        </select>
        <select name="fee_discount_id" required class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"><option value="">Select discount</option>
            @foreach($discounts as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
        </select>
        <button class="btn-primary px-4 py-2 text-sm">Assign</button>
    </form>
    <table class="w-full text-sm"><tbody>
    @forelse($assigned as $a)
        <tr class="border-t border-gray-100">
            <td class="py-2">{{ $a->first_name }} {{ $a->last_name }} <span class="text-xs text-gray-400">({{ $a->admission_no }})</span></td>
            <td>{{ $a->name }}</td>
            <td>{{ $a->discount_type === 'percent' ? rtrim(rtrim(number_format($a->value, 2), '0'), '.') . '%' : $cur . number_format($a->value, 2) }}</td>
            <td class="text-right"><form method="POST" action="{{ route('admin.fees.discounts.unassign', $a->id) }}">@csrf @method('DELETE')<button class="text-xs text-red-600 underline">Remove</button></form></td>
        </tr>
    @empty
        <tr><td class="py-3 text-gray-400">No students have discounts this year.</td></tr>
    @endforelse
    </tbody></table>
</div>
@endsection
