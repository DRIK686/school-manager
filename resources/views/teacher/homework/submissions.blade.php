@extends('layouts.teacher')
@section('title','Submissions')
@section('page-title','Homework Submissions')
@section('content')
<div class="space-y-4">
    <a href="{{ route('admin.homework.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        ← Back to Homework
    </a>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800">{{ $homework->title }}</h2>
        <p class="text-xs text-gray-400 mt-1">
            {{ $homework->subject->name }} · {{ $homework->schoolClass->name }} · Due {{ $homework->due_date->format('d M Y') }}
        </p>
        @if($homework->description)
        <p class="text-sm text-gray-600 mt-2">{{ $homework->description }}</p>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Submissions</h2>
            <span class="text-xs text-gray-400">{{ $submissions->count() }} submission(s)</span>
        </div>
        @if($submissions->isEmpty())
        <div class="px-5 py-12 text-center text-gray-400 text-sm">No submissions yet.</div>
        @else
        <div class="divide-y divide-gray-50" x-data="{}">
            @foreach($submissions as $sub)
            <div class="p-5" x-data="{ open: {{ $sub->status === 'submitted' ? 'true' : 'false' }} }">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-800">{{ $sub->student->full_name }}</p>
                            <span class="text-xs text-gray-400">{{ $sub->student->admission_no }}</span>
                            @if($sub->status === 'graded')
                                <span class="text-xs px-2 py-0.5 bg-green-50 text-green-700 rounded-full">✓ Graded {{ $sub->score }}/100</span>
                            @else
                                <span class="text-xs px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full">Awaiting grade</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">Submitted {{ $sub->submitted_at?->format('d M Y H:i') }}</p>
                    </div>
                    <button @click="open=!open"
                            class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 shrink-0">
                        <span x-text="open ? 'Hide' : 'Review'"></span>
                    </button>
                </div>

                <div x-show="open" x-cloak class="mt-4 space-y-3">
                    {{-- Student's answer --}}
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Student's Answer</p>
                        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $sub->answer }}</p>
                    </div>

                    {{-- Grade form --}}
                    <form method="POST"
                          action="{{ request()->routeIs('admin.*') ? route('admin.homework.grade', [$homework, $sub]) : route('teacher.homework.grade', [$homework, $sub]) }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Score (out of 100)</label>
                                <input type="number" name="score" value="{{ $sub->score }}" min="0" max="100" step="0.5"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none"
                                       placeholder="e.g. 85">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Feedback <span class="text-red-500">*</span></label>
                                <textarea name="feedback" rows="2" required
                                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none"
                                          placeholder="Write feedback for the student...">{{ $sub->feedback }}</textarea>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button type="submit"
                                    class="px-4 py-1.5 text-white rounded-lg text-sm font-medium"
                                    style="background:var(--sidebar-bg)">
                                {{ $sub->status === 'graded' ? 'Update Grade' : 'Submit Grade' }}
                            </button>
                        </div>
                    </form>

                    @if($sub->status === 'graded')
                    <div class="bg-green-50 rounded-lg p-3 text-xs text-green-700">
                        Last graded by {{ $sub->gradedBy?->name }} on {{ $sub->graded_at?->format('d M Y H:i') }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
