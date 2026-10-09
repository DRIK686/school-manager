@extends('layouts.teacher')
@section('title','Post Homework')
@section('page-title','Post Homework')
@section('content')
<div class="max-w-xl">
    <a href="{{ route('teacher.homework.index') }}" class="text-sm text-gray-500 mb-4 inline-block">← Back</a>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('teacher.homework.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class *</label>
                    <select name="class_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                        <option value="">Select</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ old('class_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subject *</label>
                    <select name="subject_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                        <option value="">Select</option>
                        @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ old('subject_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none"
                       placeholder="e.g. Complete exercises on page 45">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Due Date *</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description / Instructions</label>
                <textarea name="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none"
                          placeholder="Additional details...">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Attach a file <span class="text-gray-400 font-normal">(optional: PDF, Word, PowerPoint, Excel or photo, max 10 MB)</span></label>
                <input type="file" name="attachment" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png"
                    class="w-full text-sm text-gray-600 border border-gray-200 rounded-lg file:mr-3 file:py-2 file:px-4 file:border-0 file:bg-gray-100 file:text-sm">
                @error('attachment')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 text-white rounded-lg text-sm font-medium" style="background:var(--sidebar-bg)">Post Homework</button>
                <a href="{{ route('teacher.homework.index') }}" class="px-5 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
