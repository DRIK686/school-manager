@extends('layouts.teacher')
@section('title','Lesson Plans')
@section('page-title','Lesson Plans')
@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div></div>
        <a href="{{ route('teacher.lesson-plans.create') }}"
           class="px-4 py-2 text-white rounded-lg text-sm font-medium" style="background:var(--sidebar-bg)">
           + New Lesson Plan
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Week No</label>
                <input type="number" name="week_no" value="{{ request('week_no') }}" min="1" max="52"
                       placeholder="e.g. 3"
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm w-24 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All</option>
                    <option value="draft"     {{ request('status')=='draft'     ? 'selected':'' }}>Draft</option>
                    <option value="submitted" {{ request('status')=='submitted' ? 'selected':'' }}>Submitted</option>
                    <option value="needs_revision" {{ request('status')=='needs_revision' ? 'selected':'' }}>Needs Revision</option>
                    <option value="reviewed"  {{ request('status')=='reviewed'  ? 'selected':'' }}>Reviewed</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-white rounded-lg text-sm" style="background:var(--sidebar-bg)">Filter</button>
            @if(request()->hasAny(['week_no','status']))
            <a href="{{ route('teacher.lesson-plans.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">My Lesson Plans</h2>
            <span class="text-xs text-gray-400">{{ $plans->count() }} plan(s)</span>
        </div>
        @if($plans->isEmpty())
        <div class="px-5 py-16 text-center text-gray-400 text-sm">
            No lesson plans yet. <a href="{{ route('teacher.lesson-plans.create') }}" class="underline" style="color:var(--sidebar-bg)">Create one</a>.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Week</th>
                        <th class="px-5 py-3 text-left">Class</th>
                        <th class="px-5 py-3 text-left">Subject</th>
                        <th class="px-5 py-3 text-left">Topic</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Admin Remarks</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">Week {{ $plan->week_no }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $plan->schoolClass->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $plan->subject->name }}</td>
                        <td class="px-5 py-3 text-gray-800">
                            {{ $plan->topic }}
                            @if($plan->file_path)
                            <a href="{{ asset('storage/'.$plan->file_path) }}" target="_blank" title="{{ $plan->file_original_name }}" class="ml-1 text-gray-400 hover:text-gray-600">📎</a>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($plan->status === 'reviewed')
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">Reviewed</span>
                            @elseif($plan->status === 'submitted')
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">Submitted</span>
                            @elseif($plan->status === 'needs_revision')
                                <span class="px-2 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium">Needs Revision</span>
                            @else
                                <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">Draft</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $plan->admin_remarks ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($plan->status !== 'reviewed')
                            <div class="flex gap-2">
                                <a href="{{ route('teacher.lesson-plans.edit', $plan) }}"
                                   class="text-xs px-3 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</a>
                                <form method="POST" action="{{ route('teacher.lesson-plans.destroy', $plan) }}"
                                      onsubmit="return confirm('Delete this plan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-3 py-1 border border-red-200 rounded-lg text-red-500 hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">Locked</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
