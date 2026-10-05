@extends('layouts.admin')
@section('title', 'Import Students')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.students.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit mb-4">
        ← Back to Students
    </a>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Import Students from CSV</h2>
        <p class="text-sm text-gray-500 mb-5">
            Bring in existing student records in bulk — useful when onboarding a school that already has students.
        </p>

        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6 text-sm text-blue-800 space-y-2">
            <p><strong>1.</strong> Download the template below and fill it in — one row per student.</p>
            <p><strong>2.</strong> The <code>class</code> column must exactly match a class name already set up under Classes & Sections (e.g. "JHS 1").</p>
            <p><strong>3.</strong> If your data is in Excel, save it as CSV first (File → Save As → CSV).</p>
            <p><strong>4.</strong> Student PINs are not created automatically — generate them afterward from Students → PINs.</p>
            <p><strong>5.</strong> If a student already has a real admission number from your school's old records, put it in the <code>admission_no</code> column to keep it. Leave blank to auto-generate a new one.</p>
            <a href="{{ route('admin.students.import.template') }}"
               class="inline-flex items-center gap-2 mt-2 px-4 py-2 bg-white border border-blue-200 rounded-lg text-blue-700 font-medium hover:bg-blue-100">
                ⬇ Download CSV Template
            </a>
        </div>

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Available Classes</p>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($classes as $class)
            <span class="text-xs px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">{{ $class->name }}</span>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
            @csrf
            <label class="block text-xs font-medium text-gray-600 mb-1">CSV File</label>
            <input type="file" name="file" accept=".csv,.txt" required
                   class="w-full text-sm text-gray-600 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 mb-1">
            @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-gray-400 mb-4">Max 5MB.</p>
            <button type="submit" class="btn-primary px-6 py-2.5">
                Upload & Import
            </button>
        </form>
    </div>
</div>
@endsection
