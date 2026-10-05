@extends('layouts.admin')
@section('title', 'Fee Structures')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Fee Structures</h2>
        <p class="text-sm text-gray-500">Current Year: <strong>{{ $year?->name ?? 'Not set' }}</strong></p>
    </div>
    <a href="{{ route('admin.fees.categories') }}" class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">
        Manage Categories
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Create Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Add Fee Structure</h3>
        <form method="POST" action="{{ route('admin.fees.structures.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Academic Year <span class="text-red-500">*</span></label>
                <select name="academic_year_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $ay->is_current ? 'selected' : '' }}>{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Fee Category <span class="text-red-500">*</span></label>
                <select name="fee_category_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    <option value="">Select category...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class (leave blank for all classes)</label>
                <select name="class_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Amount ({{ \App\Models\SchoolSetting::current()->currency_symbol }}) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Fine Per Day ({{ \App\Models\SchoolSetting::current()->currency_symbol }})</label>
                <input type="number" name="fine_per_day" value="{{ old('fine_per_day',0) }}" min="0" step="0.01"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <button type="submit" class="btn-primary w-full py-2">Create Structure</button>
        </form>
    </div>

    {{-- Structures List --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Fee Structures — {{ $year?->name }}</h3>
        </div>
        @forelse($structures as $s)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-gray-800">{{ $s->feeCategory->name }}</p>
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
                        {{ $s->schoolClass?->name ?? 'All Classes' }}
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-sm font-bold" style="color:var(--sidebar-bg)">
                        {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ number_format($s->amount,2) }}
                    </span>
                    @if($s->due_date)
                        <span class="text-xs text-gray-400">Due: {{ $s->due_date->format('d M Y') }}</span>
                    @endif
                    @if($s->fine_per_day > 0)
                        <span class="text-xs text-red-500">Fine: {{ \App\Models\SchoolSetting::current()->currency_symbol }}{{ $s->fine_per_day }}/day</span>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('admin.fees.structures.destroy', $s) }}"
                onsubmit="return confirm('Delete this fee structure?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 ml-4">Delete</button>
            </form>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-gray-400 text-sm">No fee structures for the current year. Create one to get started.</div>
        @endforelse
    </div>
</div>
@endsection
