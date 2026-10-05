@extends('layouts.admin')
@section('title','Academic History')
@section('page-title', $student->full_name . ' — Academic History')
@section('content')

<div class="max-w-3xl">
    <a href="{{ route('admin.students.show', $student) }}" class="text-sm text-gray-500 hover:underline mb-4 inline-block">← Back to Profile</a>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Academic Year</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Class</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Section</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $e)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3">{{ $e->academicYear->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $e->schoolClass->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $e->section->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full
                            {{ $e->status === 'promoted' ? 'bg-green-50 text-green-600' : '' }}
                            {{ $e->status === 'repeated' ? 'bg-yellow-50 text-yellow-600' : '' }}
                            {{ $e->status === 'graduated' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $e->status === 'withdrawn' ? 'bg-red-50 text-red-600' : '' }}">
                            {{ ucfirst($e->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $e->remarks ?: '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm">No promotion history recorded yet — this student's current placement is their only record so far.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
