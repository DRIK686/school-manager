@extends('layouts.teacher')
@section('title','Recommend — ' . $class->name)
@section('content')

<form method="POST" action="{{ route('teacher.promotion.store') }}">
    @csrf
    <input type="hidden" name="class_id" value="{{ $class->id }}">

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-4 flex items-center justify-between">
        <div>
            <h2 class="font-bold text-gray-800 text-lg">{{ $class->name }}</h2>
            <p class="text-sm text-gray-500">{{ $students->count() }} student(s) · {{ $year->name }}</p>
        </div>
        <a href="{{ route('teacher.promotion.index') }}" class="text-sm text-gray-500 hover:underline">← Back</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Student</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Recommendation</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Suggested Class</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $student)
                @php $prior = $existing->get($student->id); @endphp
                <tr class="border-b border-gray-50" x-data="{ action: '{{ $prior->recommended_action ?? 'promote' }}' }">
                    <input type="hidden" name="students[{{ $i }}][id]" value="{{ $student->id }}">
                    <td class="px-4 py-3">{{ $student->full_name }}</td>
                    <td class="px-4 py-3">
                        <select name="students[{{ $i }}][action]" x-model="action"
                                class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                            <option value="promote">Promote</option>
                            <option value="repeat">Repeat Class</option>
                            <option value="graduate">Graduate</option>
                            <option value="withdraw">Withdraw</option>
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <select name="students[{{ $i }}][class_id]" x-show="action === 'promote'"
                                class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                            <option value="">Select...</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ (($prior->recommended_class_id ?? $nextClass->id ?? null) == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <span x-show="action !== 'promote'" class="text-xs text-gray-400 italic">—</span>
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" name="students[{{ $i }}][remarks]" value="{{ $prior->remarks ?? '' }}"
                               placeholder="Optional note for Admin"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <button type="submit" class="btn-primary text-sm px-6 py-2.5">Submit Recommendations to Admin</button>
    </div>
</form>
@endsection
