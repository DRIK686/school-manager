@extends('layouts.admin')
@section('title', 'Grade Scales')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.exams.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Exams
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Add Grade</h3>
        <p class="text-xs text-gray-400 mb-4">Year: <strong>{{ $year?->name }}</strong></p>
        <form method="POST" action="{{ route('admin.exams.grades.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Grade <span class="text-red-500">*</span></label>
                    <input type="text" name="grade" value="{{ old('grade') }}" placeholder="A1" required maxlength="5"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Points <span class="text-red-500">*</span></label>
                    <input type="number" name="points" value="{{ old('points',1) }}" min="1" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Min Mark <span class="text-red-500">*</span></label>
                    <input type="number" name="min_mark" value="{{ old('min_mark') }}" min="0" max="100" step="0.01" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Max Mark <span class="text-red-500">*</span></label>
                    <input type="number" name="max_mark" value="{{ old('max_mark') }}" min="0" max="100" step="0.01" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Remark</label>
                <input type="text" name="remark" value="{{ old('remark') }}" placeholder="Excellent, Good, Pass, Fail"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <button type="submit" class="btn-primary w-full py-2">Add Grade</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Grade Scale — {{ $year?->name }}</h3>
        </div>
        @forelse($scales as $scale)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50">
            <div class="flex items-center gap-4">
                <span class="text-2xl font-bold w-12 text-center" style="color:var(--sidebar-bg)">{{ $scale->grade }}</span>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $scale->min_mark }} — {{ $scale->max_mark }} marks</p>
                    <p class="text-xs text-gray-400">Points: {{ $scale->points }} • {{ $scale->remark }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.exams.grades.destroy', $scale) }}"
                onsubmit="return confirm('Delete this grade?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
            </form>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-gray-400 text-sm">No grade scales yet.</div>
        @endforelse
    </div>
</div>
@endsection
