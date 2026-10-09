@extends('layouts.teacher')
@section('title','Homework')
@section('page-title','Homework')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <div></div>
        <a href="{{ route('teacher.homework.create') }}" class="px-4 py-2 text-white rounded-lg text-sm font-medium" style="background:var(--sidebar-bg)">+ Post Homework</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex gap-3 items-end flex-wrap">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                <select name="class_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id')==$class->id?'selected':'' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">All Homework ({{ $homework->count() }})</h2>
        </div>
        @if($homework->isEmpty())
        <div class="px-5 py-12 text-center text-gray-400 text-sm">No homework posted yet.</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($homework as $hw)
            <div class="px-5 py-4 flex items-start justify-between gap-3">
                <div>
                    <p class="font-medium text-gray-800">{{ $hw->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $hw->schoolClass->name }} · {{ $hw->subject->name }} · {{ $hw->teacher->name }}
                        · Due {{ $hw->due_date->format('d M Y') }}
                    </p>
                    @if($hw->attachment_path)
                    <a href="{{ asset('storage/'.$hw->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:underline mt-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.415a6 6 0 108.486 8.486L20.5 13"/></svg>
                        {{ $hw->attachment_name ?? 'Attachment' }}
                    </a>
                    @endif
                    @if($hw->description)<p class="text-sm text-gray-600 mt-1">{{ Str::limit($hw->description,100) }}</p>@endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('teacher.homework.submissions', $hw) }}"
                       class="text-xs px-3 py-1 border border-blue-200 rounded-lg text-blue-600 hover:bg-blue-50">
                       Submissions ({{ $hw->submissions_count ?? 0 }})
                    </a>
                    <form method="POST" action="{{ route('teacher.homework.destroy',$hw) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs px-3 py-1 border border-red-200 rounded-lg text-red-500 shrink-0">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
