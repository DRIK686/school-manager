@extends('layouts.admin')
@section('title', 'Finance Categories')
@section('content')
@include('admin.finance._nav')
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>@endif
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="POST" action="{{ route('admin.finance.categories.store') }}" class="flex flex-wrap gap-3 items-end">@csrf
        <input name="name" required placeholder="Category name" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
        <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"><option value="expense">Expense</option><option value="income">Income</option></select>
        <button class="btn-primary px-4 py-2 text-sm">Add category</button>
    </form>
</div>
<div class="grid md:grid-cols-2 gap-4">
@foreach(['Expense categories' => $expense, 'Income categories' => $income] as $title => $list)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold mb-3 text-sm">{{ $title }}</h3>
        <table class="w-full text-sm"><tbody>
        @foreach($list as $c)
            <tr class="border-t border-gray-100 {{ $c->is_active ? '' : 'opacity-50' }}">
                <td class="py-2">{{ $c->name }} <span class="text-xs text-gray-400">({{ $c->transactions_count }})</span></td>
                <td class="text-right"><form method="POST" action="{{ route('admin.finance.categories.toggle', $c) }}">@csrf<button class="text-xs underline text-gray-600">{{ $c->is_active ? 'Hide' : 'Show' }}</button></form></td>
            </tr>
        @endforeach
        </tbody></table>
    </div>
@endforeach
</div>
@endsection
