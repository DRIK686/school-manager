@extends('layouts.teacher')
@section('title','Edit Lesson Plan')
@section('page-title','Edit Lesson Plan')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('teacher.lesson-plans.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit mb-4">
        ← Back to Lesson Plans
    </a>
    <div class="bg-blue-50 border border-blue-100 text-blue-700 text-sm rounded-lg px-4 py-3 mb-4 flex items-start gap-2">
        <span>💡</span>
        <span>Class, Subject, Week, and Topic are always required. But you don't have to type out the objectives, activities, and resources — attach a Word, PDF, or image of your prepared plan near the bottom instead, and leave those fields blank.</span>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('teacher.lesson-plans.update', $plan) }}" class="space-y-4" enctype="multipart/form-data">
            @csrf @method('PUT')

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Class <span class="text-red-500">*</span></label>
        <select name="class_id" required x-model="selectedClass"
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            <option value="">Select class</option>
            @foreach($mySubjects->unique('class_id') as $cs)
            <option value="{{ $cs->class_id }}" {{ old('class_id', $plan->class_id ?? '') == $cs->class_id ? 'selected' : '' }}>
                {{ $cs->schoolClass->name }}
            </option>
            @endforeach
        </select>
        @error('class_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Subject <span class="text-red-500">*</span></label>
        <select name="subject_id" required
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            <option value="">Select subject</option>
            @foreach($mySubjects as $cs)
            <option value="{{ $cs->subject_id }}"
                    data-class="{{ $cs->class_id }}"
                    {{ old('subject_id', $plan->subject_id ?? '') == $cs->subject_id ? 'selected' : '' }}>
                {{ $cs->subject->name }} ({{ $cs->schoolClass->name }})
            </option>
            @endforeach
        </select>
        @error('subject_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Week Number <span class="text-red-500">*</span></label>
        <input type="number" name="week_no" min="1" max="52"
               value="{{ old('week_no', $plan->week_no ?? '') }}"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
               placeholder="e.g. 3">
        @error('week_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            <option value="draft"     {{ old('status', $plan->status ?? 'draft') == 'draft'     ? 'selected' : '' }}>Draft</option>
            <option value="submitted" {{ old('status', $plan->status ?? '')      == 'submitted' ? 'selected' : '' }}>Submit for Review</option>
        </select>
    </div>
</div>
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Topic / Title <span class="text-red-500">*</span></label>
    <input type="text" name="topic" value="{{ old('topic', $plan->topic ?? '') }}"
           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
           placeholder="e.g. Introduction to Fractions">
    @error('topic')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Learning Objectives</label>
    <textarea name="objectives" rows="3"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
              placeholder="By the end of the lesson, students will be able to...">{{ old('objectives', $plan->objectives ?? '') }}</textarea>
</div>
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Activities / Methods</label>
    <textarea name="activities" rows="3"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
              placeholder="Group work, demonstration, Q&A...">{{ old('activities', $plan->activities ?? '') }}</textarea>
</div>
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Resources / Materials</label>
    <textarea name="resources" rows="2"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
              placeholder="Textbook pg 45, whiteboard, worksheets...">{{ old('resources', $plan->resources ?? '') }}</textarea>
</div>
<div class="border-t border-gray-100 pt-4">
    <label class="block text-xs font-medium text-gray-600 mb-1">Attach Prepared Plan <span class="text-gray-400 font-normal">(optional — Word, PDF, or image)</span></label>
    @if($plan->file_path)
    <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 mb-2">
        📎 <a href="{{ asset('storage/'.$plan->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $plan->file_original_name }}</a>
        <span class="text-gray-400 text-xs">(currently attached — uploading a new file will replace it)</span>
    </div>
    @endif
    <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
           class="w-full text-sm text-gray-600 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2">
    <p class="text-xs text-gray-400 mt-1">Max 10MB. If you attach a file, the fields above are optional.</p>
    @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 text-white rounded-lg text-sm font-medium" style="background:var(--sidebar-bg)">
                    Update Lesson Plan
                </button>
                <a href="{{ route('teacher.lesson-plans.index') }}" class="px-5 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
