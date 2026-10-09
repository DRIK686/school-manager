@extends('layouts.admin')
@section('title','Post Homework')
@section('page-title','Post Homework')
@section('content')
<div class="max-w-xl">
    <a href="{{ route('admin.homework.index') }}" class="text-sm text-gray-500 mb-4 inline-block">← Back</a>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.homework.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class *</label>
                    <select name="class_id" id="hw-class-select" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                        <option value="">Select</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ old('class_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subject *</label>
                    <select name="subject_id" id="hw-subject-select" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                        <option value="">Select a class first</option>
                    </select>
                    @error('subject_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <script>
                const hwClassSubjects = @json($classSubjectsMap);
                const hwOldSubjectId = @json(old('subject_id'));
                function hwRebuildSubjects() {
                    const classId = document.getElementById('hw-class-select').value;
                    const subjectSelect = document.getElementById('hw-subject-select');
                    const subjects = hwClassSubjects[classId] || [];
                    if (!classId) {
                        subjectSelect.innerHTML = '<option value="">Select a class first</option>';
                        return;
                    }
                    if (subjects.length === 0) {
                        subjectSelect.innerHTML = '<option value="">No subjects assigned to this class</option>';
                        return;
                    }
                    subjectSelect.innerHTML = '<option value="">Select</option>';
                    subjects.forEach(function(s) {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        if (String(s.id) === String(hwOldSubjectId)) opt.selected = true;
                        subjectSelect.appendChild(opt);
                    });
                }
                document.getElementById('hw-class-select').addEventListener('change', hwRebuildSubjects);
                document.addEventListener('DOMContentLoaded', hwRebuildSubjects);
            </script>
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
                <a href="{{ route('admin.homework.index') }}" class="px-5 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
