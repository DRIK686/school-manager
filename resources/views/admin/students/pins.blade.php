@extends('layouts.admin')
@section('title','Student PINs')
@section('page-title','Student Portal PINs')
@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Filter by Class</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId==$class->id?'selected':'' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($classId)
            <form method="POST" action="{{ route('admin.students.pins.reset-all') }}">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <button type="submit" onclick="return confirm('Reset ALL PINs for this class?')"
                        class="px-4 py-2 border border-amber-300 text-amber-600 rounded-lg text-sm hover:bg-amber-50">
                    Reset All PINs for Class
                </button>
            </form>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Student PINs</h2>
            <span class="text-xs text-gray-400">{{ $students->count() }} student(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Student</th>
                        <th class="px-5 py-3 text-left">Admission No</th>
                        <th class="px-5 py-3 text-left">Class</th>
                        <th class="px-5 py-3 text-left">PIN Status</th>
                        <th class="px-5 py-3 text-left">Current PIN</th>
                        <th class="px-5 py-3 text-left">Last Login</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($students as $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $student->full_name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $student->admission_no }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $student->schoolClass?->name }}</td>
                        <td class="px-5 py-3">
                            @if(!$student->pin)
                            <span class="text-xs px-2 py-0.5 bg-red-50 text-red-600 rounded-full">No PIN</span>
                            @elseif($student->pin->is_first_login)
                            <span class="text-xs px-2 py-0.5 bg-amber-50 text-amber-600 rounded-full">Not logged in yet</span>
                            @else
                            <span class="text-xs px-2 py-0.5 bg-green-50 text-green-600 rounded-full">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-mono text-gray-700">
                            {{ $student->pin?->plain_pin && $student->pin?->is_first_login ? $student->pin->plain_pin : '••••••••' }}
                        </td>
                        <td class="px-5 py-3 text-gray-400 text-xs">
                            {{ $student->pin?->last_login_at?->format('d M Y H:i') ?? 'Never' }}
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.students.pins.reset', $student) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Reset PIN for {{ $student->full_name }}?')"
                                        class="text-xs px-3 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">
                                    Reset PIN
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
        <strong>Note:</strong> The plain PIN is only visible before the student logs in for the first time and sets their own PIN.
        After first login, the PIN is hidden. Use "Reset PIN" to generate a new one if a student forgets theirs.
        Students log in at <strong>/student/login</strong> using their admission number and PIN.
    </div>
</div>
@endsection
