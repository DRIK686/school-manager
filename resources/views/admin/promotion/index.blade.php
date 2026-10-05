@extends('layouts.admin')
@section('title','Student Promotion')
@section('page-title','Student Promotion')
@section('content')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 text-lg mb-1">Promote Students to a New Academic Year</h2>
        <p class="text-sm text-gray-500 mb-6">
            Pick the class you want to review, then decide — student by student — whether each one is promoted, repeats, graduates, or is withdrawn.
        </p>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        @if(($pendingRecommendations ?? collect())->count())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="font-semibold text-sm text-blue-800 mb-2">📋 Teachers have submitted promotion recommendations for review:</p>
            <div class="space-y-1">
                @foreach($pendingRecommendations as $classId => $recs)
                <a href="{{ route('admin.promotion.recommendations', ['class_id' => $classId]) }}"
                   class="flex items-center justify-between text-sm bg-white rounded-lg px-3 py-2 hover:shadow-sm transition-all">
                    <span class="text-gray-700">{{ $recs->first()->fromClass->name ?? 'Class' }} — {{ $recs->count() }} recommendation(s)</span>
                    <span class="text-blue-600 font-semibold text-xs">Review →</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.promotion.roster') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From Academic Year</label>
                <select name="from_academic_year_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">Select academic year...</option>
                    @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>
                        {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class to Review</label>
                <select name="from_class_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">Select class...</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Section (optional — leave blank for all sections)</label>
                <select name="from_section_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All sections</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm px-6 py-2.5">Review Students →</button>
        </form>
    </div>

    <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-xs text-yellow-800">
        💡 Each student's exam results, attendance, and fee history for past years are preserved permanently and are not affected by promotion.
    </div>
</div>
@endsection
