@extends('layouts.student')
@section('title','Homework')
@section('page-title','Homework')
@section('content')
<div class="space-y-4">
    @forelse($homework as $hw)
    @php $submission = $hw->submissions->where('student_id', session('student_id'))->first(); @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-semibold text-gray-800">{{ $hw->title }}</h3>
                    @if($submission)
                        @if($submission->status === 'graded')
                            <span class="text-xs px-2 py-0.5 bg-green-50 text-green-700 rounded-full font-medium">✓ Graded</span>
                        @else
                            <span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full font-medium">Submitted</span>
                        @endif
                    @elseif($hw->due_date->isPast())
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">Past due</span>
                    @elseif($hw->due_date->isToday())
                        <span class="text-xs px-2 py-0.5 bg-red-50 text-red-600 rounded-full font-medium">Due Today!</span>
                    @elseif($hw->due_date->isTomorrow())
                        <span class="text-xs px-2 py-0.5 bg-amber-50 text-amber-600 rounded-full">Due Tomorrow</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $hw->subject->name }} · {{ $hw->teacher->name }} · Due {{ $hw->due_date->format('d M Y') }}
                </p>
                    @if($hw->attachment_path)
                    <a href="{{ asset('storage/'.$hw->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:underline mt-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.415a6 6 0 108.486 8.486L20.5 13"/></svg>
                        {{ $hw->attachment_name ?? 'Attachment' }}
                    </a>
                    @endif
                @if($hw->description)
                <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $hw->description }}</p>
                @endif
            </div>
        </div>

        {{-- Graded feedback --}}
        @if($submission?->status === 'graded')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Teacher Feedback</p>
                @if($submission->score !== null)
                <span class="text-sm font-bold text-green-700 bg-green-100 px-3 py-0.5 rounded-full">
                    Score: {{ $submission->score }}/100
                </span>
                @endif
            </div>
            <p class="text-sm text-green-800 whitespace-pre-line">{{ $submission->feedback }}</p>
            <p class="text-xs text-green-600 mt-2">
                Graded by {{ $submission->gradedBy?->name }} · {{ $submission->graded_at?->format('d M Y H:i') }}
            </p>
        </div>
        {{-- Show their answer --}}
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Your Submission</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $submission->answer }}</p>
            <p class="text-xs text-gray-400 mt-1">Submitted {{ $submission->submitted_at?->format('d M Y H:i') }}</p>
        </div>

        @elseif($submission?->status === 'submitted')
        {{-- Submitted but not graded --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
            <p class="text-xs font-semibold text-blue-700 uppercase mb-1">Your Submission (Awaiting grading)</p>
            <p class="text-sm text-blue-800 whitespace-pre-line">{{ $submission->answer }}</p>
            <p class="text-xs text-blue-600 mt-1">Submitted {{ $submission->submitted_at?->format('d M Y H:i') }}</p>
        </div>
        {{-- Allow resubmission before due date --}}
        @if(!$hw->due_date->isPast())
        <details class="mt-2">
            <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">Edit submission</summary>
            <form method="POST" action="{{ route('student.homework.submit', $hw) }}" class="mt-2 space-y-2">
                @csrf
                <textarea name="answer" rows="4" required
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                          placeholder="Update your answer...">{{ $submission->answer }}</textarea>
                <button type="submit" class="px-4 py-1.5 text-white rounded-lg text-sm font-medium" style="background:var(--sidebar-bg)">
                    Update Submission
                </button>
            </form>
        </details>
        @endif

        @else
        {{-- Not submitted yet --}}
        @if(!$hw->due_date->isPast())
        <form method="POST" action="{{ route('student.homework.submit', $hw) }}" class="mt-3 space-y-2">
            @csrf
            <label class="block text-xs font-medium text-gray-600 mb-1">Your Answer</label>
            <textarea name="answer" rows="5" required
                      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                      placeholder="Write your answer here..."></textarea>
            <button type="submit"
                    class="px-4 py-2 text-white rounded-lg text-sm font-medium"
                    style="background:var(--sidebar-bg)">
                Submit Homework
            </button>
        </form>
        @else
        <div class="mt-3 text-xs text-gray-400 italic">Submission closed — due date has passed.</div>
        @endif
        @endif
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-16 text-center text-gray-400 text-sm">
        No homework assigned yet.
    </div>
    @endforelse
</div>
@endsection
