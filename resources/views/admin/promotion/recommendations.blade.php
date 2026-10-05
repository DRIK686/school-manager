@extends('layouts.admin')
@section('title','Review Recommendations')
@section('page-title','Teacher Promotion Recommendations')
@section('content')

<div class="max-w-4xl">
    <a href="{{ route('admin.promotion.index') }}" class="text-sm text-gray-500 hover:underline mb-4 inline-block">← Back</a>

    @if($recs->isEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center text-sm text-gray-400">
        No pending recommendations for this class.
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-4">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Student</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Submitted By</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Recommendation</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Suggested Class</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recs as $rec)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3">{{ $rec->student->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $rec->teacher->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full
                            {{ $rec->recommended_action === 'promote' ? 'bg-green-50 text-green-600' : '' }}
                            {{ $rec->recommended_action === 'repeat' ? 'bg-yellow-50 text-yellow-600' : '' }}
                            {{ $rec->recommended_action === 'graduate' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $rec->recommended_action === 'withdraw' ? 'bg-red-50 text-red-600' : '' }}">
                            {{ ucfirst($rec->recommended_action) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $rec->recommendedClass->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $rec->remarks ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('admin.promotion.roster') }}">
        @csrf
        <input type="hidden" name="from_academic_year_id" value="{{ $recs->first()->from_academic_year_id }}">
        <input type="hidden" name="from_class_id" value="{{ $recs->first()->from_class_id }}">
        <button type="submit" class="btn-primary text-sm px-6 py-2.5">
            Open Full Roster (pre-filled with these recommendations) →
        </button>
    </form>
    @endif
</div>
@endsection
