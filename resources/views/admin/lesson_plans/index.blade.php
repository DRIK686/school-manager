@extends('layouts.admin')
@section('title','Lesson Plans')
@section('page-title','Lesson Plans — Review')
@section('content')
<div class="space-y-4">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Teacher</label>
                <select name="teacher_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
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
            @if(request()->hasAny(['teacher_id','status']))
            <a href="{{ route('admin.lesson-plans.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">All Lesson Plans</h2>
            <span class="text-xs text-gray-400">{{ $plans->count() }} plan(s)</span>
        </div>
        @if($plans->isEmpty())
        <div class="px-5 py-16 text-center text-gray-400 text-sm">No lesson plans submitted yet.</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($plans as $plan)
            <div class="px-5 py-4" x-data="{ open: false }">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium text-gray-800">{{ $plan->topic }}</span>
                            @if($plan->status === 'reviewed')
                                <span class="px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs">Reviewed</span>
                            @elseif($plan->status === 'submitted')
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs">Submitted</span>
                            @elseif($plan->status === 'needs_revision')
                                <span class="px-2 py-0.5 bg-red-50 text-red-700 rounded-full text-xs">Needs Revision</span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full text-xs">Draft</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Week {{ $plan->week_no }} · {{ $plan->schoolClass->name }} · {{ $plan->subject->name }} · {{ $plan->teacher->name }}
                        </p>
                    </div>
                    <button @click="open=!open" class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 shrink-0">
                        Details
                    </button>
                </div>

                <div x-show="open" x-cloak class="mt-4 space-y-3 border-t border-gray-50 pt-4">
                    @if($plan->objectives)
                    <div><p class="text-xs font-semibold text-gray-500 uppercase mb-1">Objectives</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $plan->objectives }}</p></div>
                    @endif
                    @if($plan->activities)
                    <div><p class="text-xs font-semibold text-gray-500 uppercase mb-1">Activities</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $plan->activities }}</p></div>
                    @endif
                    @if($plan->resources)
                    <div><p class="text-xs font-semibold text-gray-500 uppercase mb-1">Resources</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $plan->resources }}</p></div>
                    @endif
                    @if($plan->file_path)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Attached File</p>
                        <a href="{{ asset('storage/'.$plan->file_path) }}" target="_blank"
                           class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline bg-blue-50 px-3 py-2 rounded-lg">
                            📎 {{ $plan->file_original_name }}
                        </a>
                    </div>
                    @endif

                    @if($plan->status === 'submitted')
                    <form method="POST" action="{{ route('admin.lesson-plans.review', $plan) }}" x-data="{ remarks: '' }">
                        @csrf
                        <label class="block text-xs font-medium text-gray-600 mb-1">Remarks <span class="text-gray-400 font-normal">(required if sending back for correction)</span></label>
                        <input type="text" name="admin_remarks" x-model="remarks" placeholder="Feedback for teacher..."
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none mb-2">
                        <div class="flex gap-3">
                            <button type="submit" name="action" value="return"
                                    @click="if(!remarks.trim()){ $event.preventDefault(); alert('Please add remarks so the teacher knows what to correct.'); }"
                                    class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm shrink-0 hover:bg-red-50">
                                ↩ Send Back for Correction
                            </button>
                            <button type="submit" name="action" value="approve"
                                    class="px-4 py-2 text-white rounded-lg text-sm shrink-0" style="background:var(--sidebar-bg)">
                                Mark Reviewed
                            </button>
                        </div>
                    </form>
                    @elseif($plan->status === 'needs_revision' && $plan->admin_remarks)
                    <div class="bg-red-50 px-4 py-3 rounded-lg text-sm text-red-700">
                        <strong>Sent back for correction:</strong> {{ $plan->admin_remarks }}
                        <p class="text-xs text-red-500 mt-1">Waiting for the teacher to update and resubmit.</p>
                    </div>
                    @elseif($plan->admin_remarks)
                    <div class="bg-green-50 px-4 py-3 rounded-lg text-sm text-green-700">
                        <strong>Admin remarks:</strong> {{ $plan->admin_remarks }}
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
