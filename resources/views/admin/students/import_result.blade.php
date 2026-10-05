@extends('layouts.admin')
@section('title', 'Import Results')
@section('content')
<div class="max-w-2xl space-y-4">
    <a href="{{ route('admin.students.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        ← Back to Students
    </a>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Import Complete</h2>
        <p class="text-sm text-gray-500 mb-5">
            {{ count($succeeded) }} student(s) imported successfully
            @if(count($failed) > 0), {{ count($failed) }} row(s) failed @endif.
        </p>

        @if(count($succeeded) > 0)
        <div class="mb-6">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-2">✓ Successfully Imported</p>
            <div class="bg-green-50 border border-green-100 rounded-lg p-4 max-h-64 overflow-y-auto">
                <ul class="text-sm text-green-800 space-y-1">
                    @foreach($succeeded as $line)
                    <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if(count($failed) > 0)
        <div class="mb-6">
            <p class="text-xs font-semibold text-red-700 uppercase tracking-wide mb-2">✕ Failed Rows — Fix and Re-upload These</p>
            <div class="bg-red-50 border border-red-100 rounded-lg p-4 max-h-64 overflow-y-auto">
                <ul class="text-sm text-red-800 space-y-1">
                    @foreach($failed as $line)
                    <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="flex gap-3 pt-2">
            <a href="{{ route('admin.students.index') }}" class="btn-primary px-6 py-2.5">View Students</a>
            <a href="{{ route('admin.students.import.form') }}" class="px-6 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-600">
                Import More
            </a>
        </div>
    </div>
</div>
@endsection
