@extends('layouts.teacher')
@section('title','Promotion Recommendations')
@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 text-lg mb-1">Recommend Students for Promotion</h2>
        <p class="text-sm text-gray-500 mb-6">
            Pick one of your classes to review. Your recommendations will be sent to the Admin, who makes the final decision and processes the actual promotion.
        </p>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('teacher.promotion.roster') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                <select name="class_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">Select one of your classes...</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm px-6 py-2.5">Review Class →</button>
        </form>
    </div>
</div>
@endsection
